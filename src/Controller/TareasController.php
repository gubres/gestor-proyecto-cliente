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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SecurityBundle\Security;

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
            'tareas' => $tareasRepository->findNotDeleted(),
        ]);
    }

    #[Route('/new', name: 'app_tareas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $tarea = new Tareas();
        $form = $this->createForm(TareasType::class, $tarea);
        $form->handleRequest($request);
    
        // Crear el formulario para nuevo proyecto
        $proyecto = new Proyectos();
        $proyectoForm = $this->createForm(ProyectosType::class, $proyecto);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Asignar el usuario actual a la tarea
            $usuarioActual = $this->getUser();
            $tarea->setCreadoPor($usuarioActual);
            $tarea->setActualizadoPor($usuarioActual);
            
            // Guardar la tarea en la base de datos
            $entityManager->persist($tarea);
            $entityManager->flush();
    
            // Redirigir a la página de índice de tareas
            return $this->redirectToRoute('app_tareas_index');
        }
    
        return $this->render('tareas/new.html.twig', [
            'form' => $form->createView(),
            'proyectoForm' => $proyectoForm->createView(),
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
            $tarea->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));
            $tarea->setDescripcion($form->get('descripcion')->getData());
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_tareas_index', [], Response::HTTP_SEE_OTHER);
        }
    
        // Crear el formulario para nuevo proyecto
        $proyecto = new Proyectos();
        $proyectoForm = $this->createForm(ProyectosType::class, $proyecto);
    
        return $this->render('tareas/edit.html.twig', [
            'tarea' => $tarea,
            'form' => $form->createView(),
            'proyectoForm' => $proyectoForm->createView(), // Asegúrate de pasar 'proyectoForm'
        ]);
    }
    

    #[Route('/delete/{id}', name: 'app_tareas_delete', methods: ['POST'])]
    public function delete(Request $request, Tareas $tarea, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tarea->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tarea);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tareas_index');
    }
}
