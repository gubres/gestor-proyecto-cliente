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
use DateTime;

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
    public function index(TareasRepository $tareasRepository, Request $request): Response
    {
        $startDateParam = $request->query->get('start');
        $endDateParam = $request->query->get('end');

        $startDate = $startDateParam ? new DateTime($startDateParam) : null;
        $endDate = $endDateParam ? new DateTime($endDateParam) : null;

        if ($startDate && $endDate) {
            // Utilizar el método nuevo para obtener tareas según el rango de fechas
            $tareas = $this->tareasRepository->findByDateRangeAndStatus($startDate, $endDate, false);
        } else {
            // Obtener todas las tareas que no están eliminadas si no se especifica un rango de fechas
            $tareas = $this->tareasRepository->findNotDeleted();
        }

        return $this->render('tareas/index.html.twig', [
            'tareas' => $tareasRepository->findNotDeleted(),
        ]);
    }

    #[Route('/new', name: 'app_tareas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $tarea = new Tareas();
        $proyecto = new Proyectos();
        $form = $this->createForm(TareasType::class, $tarea);
        $proyectoForm = $this->createForm(ProyectosType::class, $proyecto);
        $proyectoForm->handleRequest($request);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tarea->setCreadoPor($security->getUser());
            $tarea->setActualizadoPor($security->getUser());
            $tarea->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));
            $tarea->setCreadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));

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
        $proyectoForm = $this->createForm(ProyectosType::class);
        $proyectoForm->handleRequest($request);
        // Obtener el proyecto asociado a la tarea
        $proyecto = $tarea->getProyecto();

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
            'proyectoForm' => $proyectoForm->createView(),
        ]);
    }


    #[Route('/eliminar_tareas', name: 'eliminar_tareas', methods: ['POST'])]
    public function eliminarTareas(Request $request, EntityManagerInterface $entityManager, Security $security)
    {
        // Decodificar el contenido JSON de la solicitud
        $data = json_decode($request->getContent(), true);
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


    #[Route('/delete/{id}', name: 'app_tareas_delete', methods: ['POST'])]
    public function delete(Request $request, Tareas $tarea, EntityManagerInterface $entityManager): Response
    {
        $id = $request->attributes->get('id');
        $tarea = $this->tareasRepository->find($id);

        if (!$tarea) {
            throw $this->createNotFoundException('Tarea no encontrado');
        }

        if ($this->isCsrfTokenValid('delete' . $tarea->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tarea);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tareas_index');
    }
}
