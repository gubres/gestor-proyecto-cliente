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
    public function index(Request $request, TareasRepository $tareasRepository): Response
    {
        $fechaInicio = $request->query->get('fecha_inicio');
        $fechaFin = $request->query->get('fecha_fin');

        
        $tareas = $this->tareasRepository->findAll();
        // Inicializar variable para almacenar mensajes de error
        $error = null;

       // Verificar si se proporcionan fechas válidas
        if ($fechaInicio && $fechaFin) {
            // Filtrar tareas por rango de fechas si se proporcionan
            $tareas = $tareasRepository->findByDateRange($fechaInicio, $fechaFin);

            // Verificar si se encontraron tareas en el rango de fechas
            if (empty($tareas)) {
                $error = 'No se encontraron tareas en el rango de fechas proporcionado.';
            }
        } else {
            // Si no se proporcionan fechas, obtener todas las tareas
            $tareas = $tareasRepository->findNotDeleted();
        }

        // Devolver las tareas filtradas
        return $this->render('tareas/index.html.twig', [
            'tareas' => $tareas,
            'error' => $error,
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


        if (is_null($data) || !isset($data['ids'])) {
            return new JsonResponse(['error' => 'Datos inválidos. JSON malformado.'], Response::HTTP_BAD_REQUEST);
        }
        $tareasIds = $data['ids'];
        $usuarioActual = $security->getUser(); // Obtener el usuario actual con Symfony Security

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

            $entityManager->persist($tarea); // Esto puede no ser necesario dependiendo de la configuración de Doctrine
        }

        // Guardar los cambios en la base de datos
        $entityManager->flush();

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
