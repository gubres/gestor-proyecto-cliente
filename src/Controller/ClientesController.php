<?php

namespace App\Controller;

use App\Entity\Clientes;
use App\Form\ClientesType;
use App\Repository\ClientesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/clientes')]
#[IsGranted('ROLE_USER')]
class ClientesController extends AbstractController
{
    private $clientesRepository;

    public function __construct(ClientesRepository $clientesRepository, EntityManagerInterface $entityManager, Security $security)
    {
        $this->clientesRepository = $clientesRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/', name: 'app_clientes_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $clientes = $this->clientesRepository->findNotDeleted();
        $clientesTabla = $this->obtenerDatosClientes(); // Obtener los datos de los clientes para la tabla

        return $this->render('clientes/index.html.twig', [
            'clientes' => $clientes,
            'clientesTabla' => $clientesTabla,

        ]);
    }

    #[Route('/new', name: 'app_clientes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $cliente = new Clientes();
        $form = $this->createForm(ClientesType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar el usuario actual como creador del cliente
            $cliente->setCreadoPor($security->getUser());
            $cliente->setActualizadoPor($security->getUser());
            $cliente->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));
            $cliente->setCreadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));

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
            $cliente = $this->entityManager->getRepository(Clientes::class)->find($nuevoDato['id']);
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
    #[Route('/eliminar_clientes', name: 'eliminar_clientes', methods: ['POST'])]
    public function eliminarClientes(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Decodificar el contenido JSON de la solicitud
        $data = json_decode($request->getContent(), true);
        $clientesIds = $data['ids'];

        $usuarioActual = $security->getUser(); // Obtener el usuario actual con Symfony Security

        // Obtener el repositorio de clientes
        $clientesRepository = $entityManager->getRepository(Clientes::class);

        foreach ($clientesIds as $clienteId) {
            // Buscar el cliente por su ID
            $cliente = $clientesRepository->find($clienteId);

            if (!$cliente) {
                // Si el cliente no existe, devuelve una respuesta de error
                return new JsonResponse(['error' => 'Cliente no encontrado: ' . $clienteId], 404);
            }

            // Realizar borrado lógico
            $cliente->setEliminado(true);
            $cliente->setActualizadoPor($usuarioActual);
            $cliente->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));

            $entityManager->persist($cliente);
        }

        // Guardar los cambios en la base de datos
        $entityManager->flush();

        // Devolver una respuesta exitosa
        return new JsonResponse(['message' => 'Clientes actualizados como eliminados correctamente'], JsonResponse::HTTP_OK);
    }


    #[Route('/delete/{id}', name: 'app_clientes_delete', methods: ['POST'])]
    public function delete(Request $request, Clientes $cliente, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $cliente->getId(), $request->request->get('_token'))) {
            // Manejar token CSRF inválido si es necesario
        }

        // Cambiar el estado del cliente a "inactivo" o "eliminado"
        $cliente->setEliminado('eliminado');
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
}
