<?php

namespace App\Controller;

use App\Entity\Tareas;
use App\Form\TareasType;
use App\Form\ProyectosType;
use App\Entity\Proyectos;
use App\Repository\TareasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tareas')]
#[IsGranted('ROLE_USER')]
class TareasController extends AbstractController
{

    private $tareasRepository;
    private $entityManager;

    public function __construct(TareasRepository $tareasRepository, EntityManagerInterface $entityManager)
    {
        $this->tareasRepository = $tareasRepository;
        $this->entityManager = $entityManager;
    }



    #[Route('/', name: 'app_tareas_index', methods: ['GET'])]
    public function index(TareasRepository $tareasRepository): Response
    {
        return $this->render('tareas/index.html.twig', [
            'tareas' => $tareasRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_tareas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tarea = new Tareas();
        $proyecto = new Proyectos();
        $form = $this->createForm(TareasType::class, $tarea);
        $proyectoForm = $this->createForm(ProyectosType::class, $proyecto);
        $proyectoForm->handleRequest($request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tarea);
            $entityManager->flush();

            return $this->redirectToRoute('app_tareas_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tareas/new.html.twig', [
            'proyectoForm' => $proyectoForm->createView(),
            'tarea' => $tarea,
            'form' => $form,
        ]);

        if ($proyectoForm->isSubmitted() && $proyectoForm->isValid()) {
            // Persistir el nuevo proyecto
            $entityManager->persist($proyecto);
            $entityManager->flush();
    
            // Obtener la URL de la página de crear nueva tarea
            $urlNuevaTarea = $this->generateUrl('app_tareas_new');
    
            // Devolver la URL de la página de crear nueva tarea en formato JSON
            return new JsonResponse(['urlNuevaTarea' => $urlNuevaTarea]);
        }
    
        // En caso de error, devolver una respuesta de error
        return new JsonResponse(['error' => 'Error al guardar el nuevo proyecto'], Response::HTTP_BAD_REQUEST);
    
    }

    #[Route('/{id}', name: 'app_tareas_show', methods: ['GET'])]
    public function show(Tareas $tarea): Response
    {
        return $this->render('tareas/show.html.twig', [
            'tarea' => $tarea,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tareas_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tareas $tarea, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TareasType::class, $tarea);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar el proyecto seleccionado a la tarea
            $proyectoSeleccionado = $form->get('proyecto')->getData();
            $tarea->setProyecto($proyectoSeleccionado);
    
            // Guardar la tarea
            $entityManager->flush();
    
            return $this->redirectToRoute('app_tareas_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('tareas/edit.html.twig', [
            'tarea' => $tarea,
            'form' => $form->createView(),
        ]);
    }
    
    

    #[Route('/{id}', name: 'app_tareas_delete', methods: ['POST'])]
    public function delete(Request $request, Tareas $tarea, EntityManagerInterface $entityManager): Response
    {
        $id = $request->attributes->get('id');
        $tarea = $this->tareasRepository->find($id);

        if (!$tarea) {
            throw $this->createNotFoundException('Tarea no encontrado');
        }

        if ($this->isCsrfTokenValid('delete'.$tarea->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tarea);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tareas_index');
    }

    #[Route('/eliminartareas', name: 'eliminar_tareas', methods: ['POST'])]
    public function eliminarTareas(Request $request, EntityManagerInterface $entityManager)
    {
        // Obtener los IDs de las tareas a eliminar de la solicitud
        $ids = $request->request->get('tareas_seleccionadas');

        // Verificar si se enviaron IDs
        if (!empty($ids)) { 
            try {
                // Buscar y eliminar las tareas por sus IDs
                foreach ($ids as $id) {
                    $tarea = $entityManager->getRepository(Tareas::class)->find($id);
                    if ($tarea) {
                        $entityManager->remove($tarea);
                    }
                }
                // Confirmar los cambios en la base de datos
                $entityManager->flush();
                // Devolver una respuesta HTTP 200 indicando éxito
                return new Response('Tareas eliminadas correctamente', Response::HTTP_OK);
            } catch (\Exception $e) {
                // En caso de error, devolver una respuesta HTTP 500
                return new Response('Ha ocurrido un error al eliminar las tareas.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            // Si no se enviaron IDs, devolver un error 400 (Bad Request)
            return new Response('No se proporcionaron IDs de tarea para eliminar', Response::HTTP_BAD_REQUEST);
        }
    }


}
