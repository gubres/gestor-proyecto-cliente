<?php

namespace App\Controller;

use App\Entity\Clientes;
use App\Entity\Proyectos;
use App\Form\ClientesType;
use App\Form\ProyectosType;
use App\Entity\UsuariosProyectos;
use App\Repository\UsuariosRepository;
use App\Repository\ProyectosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/proyectos')]
#[IsGranted('ROLE_USER')]
class ProyectosController extends AbstractController
{

    private $proyectosRepository;
    private $entityManager;
    private $usuariosRepository;

    public function __construct(EntityManagerInterface $entityManager, UsuariosRepository $userRepository, ProyectosRepository $proyectosRepository)
    {
        $this->entityManager = $entityManager;
        $this->usuariosRepository = $userRepository;
        $this->proyectosRepository = $proyectosRepository;
    }

    #[Route('/', name: 'app_proyectos_index', methods: ['GET'])]
    public function index(ProyectosRepository $proyectosRepository, EntityManagerInterface $entityManager): Response
    {
        $proyectos = $entityManager->getRepository(Proyectos::class)->findNotDeleted();

        // Verifica si hay proyectos, si no, crea un array vacío
        if (empty($proyectos)) {
            $proyectos = [];
        }

        return $this->render('proyectos/index.html.twig', [
            'pageName' => 'Proyectos',
            'proyectos' => $proyectos,
        ]);
    }


    #[Route('/new', name: 'app_proyectos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $proyecto = new Proyectos();
        $cliente = new clientes();
        $form = $this->createForm(ProyectosType::class, $proyecto);
        $form->handleRequest($request);
        $clienteForm = $this->createForm(ClientesType::class, $cliente);
        $clienteForm->handleRequest($request);
        $cliente = $proyecto->getCliente();

        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar los usuarios seleccionados al proyecto
            $usuariosSeleccionados = $form->get('usuarios')->getData();
            foreach ($usuariosSeleccionados as $usuario) {
                $usuarioProyecto = new UsuariosProyectos($usuario, $proyecto);
                $proyecto->addUsuariosProyectos($usuarioProyecto);
            }
            // Guardar el proyecto
            $proyecto->setCreadoPor($security->getUser());
            $proyecto->setActualizadoPor($security->getUser());
            $proyecto->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));
            $proyecto->setCreadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));

            $entityManager->persist($proyecto);
            $entityManager->flush();

            return $this->redirectToRoute('app_proyectos_index');
        }

        return $this->render('proyectos/new.html.twig', [
            'pageName' => 'Nuevo Proyecto',
            'formProyecto' => $form->createView(),
            'clienteForm' => $clienteForm->createView(),
            'proyecto' => $proyecto,
        ]);

        if ($clienteForm->isSubmitted() && $clienteForm->isValid()) {
            // Persistir el nuevo cliente
            $entityManager->persist($cliente);
            $entityManager->flush();

            // Obtener la URL de la página de crear nuevo cliente
            $urlNuevoCliente = $this->generateUrl('app_clientes_new');

            // Devolver la URL de la página de crear nuevo cliente en formato JSON
            return new JsonResponse(['urlNuevoCliente' => $urlNuevoCliente]);
        }

        // En caso de error, devolver una respuesta de error
        return new JsonResponse(['error' => 'Error al guardar el nuevo cliente'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/{id}', name: 'app_proyectos_show', methods: ['GET'])]
    public function show(Proyectos $proyecto): Response
    {
        return $this->render('proyectos/show.html.twig', [
            'proyecto' => $proyecto,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_proyectos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Proyectos $proyecto, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProyectosType::class, $proyecto);
        $form->handleRequest($request);
        $clienteForm = $this->createForm(ClientesType::class);
        $clienteForm->handleRequest($request);
        $cliente = $proyecto->getCliente();

        if ($form->isSubmitted() && $form->isValid()) {
            // Obtener los usuarios seleccionados del formulario
            $usuariosSeleccionados = $form->get('usuarios')->getData();

            // Obtener las relaciones actuales del proyecto
            $usuariosProyectosActuales = $proyecto->getUsuariosProyectos()->toArray();

            // Iterar sobre los usuarios seleccionados
            foreach ($usuariosSeleccionados as $usuarioSeleccionado) {
                // Flag para indicar si se debe crear una nueva relación para este usuario
                $crearNuevaRelacion = true;

                // Iterar sobre las relaciones actuales del proyecto asociadas a este usuario
                foreach ($usuariosProyectosActuales as $usuarioProyecto) {
                    if ($usuarioProyecto->getUsuario() === $usuarioSeleccionado) {
                        // Si la relación existente tiene estado 1, no se crea una nueva relación
                        if ($usuarioProyecto->getEstado()) {
                            $crearNuevaRelacion = false;
                        }
                        // Salir del bucle interno una vez que se encuentra una relación para este usuario
                        break;
                    }
                }

                // Si se debe crear una nueva relación para este usuario
                if ($crearNuevaRelacion) {
                    // Iterar sobre las relaciones actuales del proyecto asociadas a este usuario
                    foreach ($usuariosProyectosActuales as $usuarioProyecto) {
                        // Si la relación existente tiene estado 1, no se crea una nueva relación
                        if ($usuarioProyecto->getUsuario() === $usuarioSeleccionado && $usuarioProyecto->getEstado()) {
                            $crearNuevaRelacion = false;
                            break;
                        }
                    }

                    // Si todavía se debe crear una nueva relación, se crea y se persiste
                    if ($crearNuevaRelacion) {
                        $nuevaRelacionUsuarioProyecto = new UsuariosProyectos($usuarioSeleccionado, $proyecto);
                        $nuevaRelacionUsuarioProyecto->setEstado(true);
                        $nuevaRelacionUsuarioProyecto->setFechaAlta(new \DateTime());
                        $proyecto->addUsuariosProyectos($nuevaRelacionUsuarioProyecto);
                        $entityManager->persist($nuevaRelacionUsuarioProyecto);
                    }
                }
            }

            // Desactivar las relaciones que no están presentes en los usuarios seleccionados
            foreach ($usuariosProyectosActuales as $usuarioProyecto) {
                if (!in_array($usuarioProyecto->getUsuario(), $usuariosSeleccionados->toArray(), true)) {
                    $usuarioProyecto->setEstado(false);
                    $usuarioProyecto->setFechaBaja(new \DateTime());
                    $entityManager->persist($usuarioProyecto);
                }
            }

            // Hacer flush para guardar los cambios en la base de datos
            $entityManager->flush();

            return $this->redirectToRoute('app_proyectos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('proyectos/edit.html.twig', [
            'proyecto' => $proyecto,
            'form' => $form,
            'clienteForm' => $clienteForm->createView(),
        ]);
    }



    #[Route('/eliminar_proyectos', name: 'eliminar_proyectos', methods: ['POST'])]
    public function eliminarProyectos(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Decodificar el contenido JSON de la solicitud
        $data = json_decode($request->getContent(), true);
        if (null === $data || !isset($data['ids'])) {
            return new JsonResponse(['error' => 'Datos incorrectos o faltantes'], Response::HTTP_BAD_REQUEST);
        }
        $proyectosIds = $data['ids'];

        $usuarioActual = $security->getUser(); // Obtener el usuario actual con Symfony Security

        foreach ($proyectosIds as $proyectoId) {
            // Buscar la tarea por su ID
            $proyecto = $entityManager->getRepository(Proyectos::class)->find($proyectoId);

            if (!$proyecto) {
                return new JsonResponse(['error' => 'Tarea no encontrada con el ID: ' . $proyectoId], 404);
            }

            // Realizar borrado lógico
            $proyecto->setEliminado(true);
            $proyecto->setActualizadoPor($usuarioActual); // Asignar el usuario que hace la actualización
            $proyecto->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid'))); // Asignar la fecha actual

            $entityManager->persist($proyecto); // Esto puede no ser necesario dependiendo de la configuración de Doctrine
        }

        // Guardar los cambios en la base de datos
        $entityManager->flush();

        return new JsonResponse(['message' => 'Tareas actualizadas como eliminadas correctamente.']);
    }


    #[Route('/delete/{id}', name: 'app_proyectos_delete', methods: ['POST'])]
    public function delete(Request $request, Proyectos $proyecto, EntityManagerInterface $entityManager): Response
    {
        $id = $request->attributes->get('id');
        $proyecto = $this->proyectosRepository->find($id);

        if (!$proyecto) {
            throw $this->createNotFoundException('Proyecto no encontrado');
        }

        if ($this->isCsrfTokenValid('delete' . $proyecto->getId(), $request->request->get('_token'))) {
            $entityManager->remove($proyecto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_proyectos_index');
    }
}
