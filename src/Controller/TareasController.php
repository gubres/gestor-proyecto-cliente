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
            $tarea->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));
            $tarea->setCreadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));

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


    #[Route('/eliminar_tareas', name: 'eliminar_tareas', methods: ['POST'])]
    public function eliminarTareas(Request $request, EntityManagerInterface $entityManager, Security $security)
    {
        // Decodificar el contenido JSON de la solicitud
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['message' => 'Error en el formato JSON: ' . json_last_error_msg()], 400);
        }

        // Verificar si 'ids' está presente y es un array
        if (empty($data['ids']) || !is_array($data['ids'])) {
            return new JsonResponse(['message' => 'No se proporcionaron IDs válidos, no se realizó ninguna acción.'], 200);
        }

        $tareasIds = $data['ids'];

        $usuarioActual = $security->getUser(); // Obtener el usuario actual con Symfony Security
        $tareasProcesadas = 0;
        foreach ($tareasIds as $tareaId) {
            // Buscar la tarea por su ID
            $tarea = $entityManager->getRepository(Tareas::class)->find($tareaId);

            if (!$tarea) {
                return new JsonResponse(['error' => 'Tarea no encontrada con el ID: ' . $tareaId], 404);
            }

            // Realizar borrado lógico
            $tarea->setEliminado(true);
            $tarea->setActualizadoPor($usuarioActual); // Asignar el usuario que hace la actualización
            $tarea->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid'))); // Asignar la fecha actual

            $entityManager->persist($tarea);
            $tareasProcesadas++;
        }

        // Guardar los cambios en la base de datos
        if ($tareasProcesadas > 0) {
            $entityManager->flush();
        }

        return new JsonResponse(['message' => 'Tareas actualizadas como eliminadas correctamente.']);
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
