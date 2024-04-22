<?php

namespace App\Controller;

use App\Entity\Clientes;
use App\Form\ClientesType;
use App\Repository\ClientesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clientes')]
class ClientesController extends AbstractController
{
    private $clientesRepository;

    public function __construct(ClientesRepository $clientesRepository, EntityManagerInterface $entityManager)
    {
        $this->clientesRepository = $clientesRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_clientes_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $clientes = $this->clientesRepository->findAll();
        $clientesTabla = $this->obtenerDatosClientes(); // Obtener los datos de los clientes para la tabla

        return $this->render('clientes/index.html.twig', [
            'clientes' => $clientes,
            'clientesTabla' => $clientesTabla,
            
        ]);
    }

    #[Route('/new', name: 'app_clientes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cliente = new Clientes();
        $form = $this->createForm(ClientesType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cliente);
            $entityManager->flush();

            return $this->redirectToRoute('app_clientes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clientes/new.html.twig', [
            'cliente' => $cliente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_clientes_show', methods: ['GET'])]
    public function show(Clientes $cliente): Response
    {
        return $this->render('clientes/show.html.twig', [
            'cliente' => $cliente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_clientes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Clientes $cliente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientesType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_clientes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clientes/edit.html.twig', [
            'cliente' => $cliente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_clientes_delete', methods: ['POST'])]
    public function delete(Request $request, Clientes $cliente, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$cliente->getId(), $request->request->get('_token'))) {
            // Manejar token CSRF inválido si es necesario
        }
    
        // Cambiar el estado del cliente a "inactivo" o "eliminado"
        $cliente->setEstado('eliminado');
        $entityManager->persist($cliente);
    
        // Cambiar el estado de los proyectos asociados
        $proyectos = $cliente->getProyectos();
        foreach ($proyectos as $proyecto) {
            $proyecto->setEstado('eliminado');
            $entityManager->persist($proyecto);
        }
    
        // Guardar los cambios en la base de datos
        $entityManager->flush();
    
        return $this->redirectToRoute('app_clientes_index');
    }
    

    public function obtenerDatosClientes(): array
    {

        return $this->clientesRepository->findAll();
    }


    // Método para actualizar los datos de los clientes
    private function actualizarDatos($nuevosDatos)
    {
        // Verificar si $nuevosDatos es nulo
        if ($nuevosDatos === null) {
            return; // Salir del método si $nuevosDatos es nulo
        }

        foreach ($nuevosDatos as $nuevoDato) {
            // Supongamos que $nuevoDato es un array asociativo con los datos del cliente
            // Por ejemplo: ['id' => 1, 'nombre' => 'Nuevo nombre', 'telefono' => 'Nuevo teléfono', 'email' => 'Nuevo email']

            // Buscar el cliente en la base de datos por su ID
            $cliente = $this->entityManager->getRepository(Cliente::class)->find($nuevoDato['id']);

            // Si el cliente existe, actualizar sus datos
            if ($cliente) {
                $cliente->setNombre($nuevoDato['nombre']);
                $cliente->setTelefono($nuevoDato['telefono']);
                $cliente->setEmail($nuevoDato['email']);

                // Persistir los cambios en la base de datos
                $this->entityManager->flush();
            }
        }
    }
    #[Route('/eliminarclientes', name: 'eliminar_clientes', methods: ['POST'])]
    public function eliminarClientes(Request $request, Clientes $cliente, EntityManagerInterface $entityManager): Response
    {
        // Obtener el EntityManager
    $entityManager = $this->getDoctrine()->getManager();
        // Recuperar los IDs de los clientes seleccionados desde la solicitud AJAX
    $clientesIdsString = $request->request->get('clientes', '');
    $clientesIds = json_decode($clientesIdsString, true); // Convertir de cadena JSON a array

    // Verificar si se recibieron clientes para eliminar
    if (empty($clientesIds)) {
        return new JsonResponse(['message' => 'No se proporcionaron clientes para eliminar'], JsonResponse::HTTP_BAD_REQUEST);
    }

    // Obtener el repositorio de clientes
    $clientesRepository = $entityManager->getRepository(Clientes::class);

    // Iterar sobre los IDs de clientes y eliminarlos uno por uno
    foreach ($clientesIds as $clienteId) {
        // Buscar el cliente por su ID
        $cliente = $clientesRepository->find($clienteId);

        if (!$cliente) {
            // Si el cliente no existe, devuelve una respuesta de error
            return new JsonResponse(['message' => 'Cliente no encontrado: ' . $clienteId], JsonResponse::HTTP_NOT_FOUND);
        }

        // Eliminar el cliente
        $entityManager->remove($cliente);
    }

    // Aplicar los cambios en la base de datos
    $entityManager->flush();

    // Devolver una respuesta exitosa
    return new JsonResponse(['message' => 'Clientes eliminados correctamente'], JsonResponse::HTTP_OK);
}

}