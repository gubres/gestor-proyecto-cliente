<?php

namespace App\Controller;

use App\Entity\Tareas;
use App\Form\TareasType;
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

    public function __construct(TareasRepository $tareasRepository)
    {
        $this->tareasRepository = $tareasRepository;
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
        $form = $this->createForm(TareasType::class, $tarea);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tarea);
            $entityManager->flush();

            return $this->redirectToRoute('app_tareas_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tareas/new.html.twig', [
            'tarea' => $tarea,
            'form' => $form,
        ]);
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
    public function eliminarTareas(Request $request, Tareas $tarea, EntityManagerInterface $entityManager)
    {
         $id = $request->request->get('id'); 
        $tarea = $this->tareasRepository->find($id);

        if ($tarea) { 
            try {
                $entityManager->remove($tarea);
                $entityManager->flush();
                // Devuelve una respuesta HTTP 200 indicando éxito
                return new Response('Tareas eliminadas correctamente', Response::HTTP_OK);
            } catch (\Exception $e) {
                // En caso de error, devuelve una respuesta HTTP 500
                return new Response('Ha ocurrido un error al eliminar las tareas.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }


}
