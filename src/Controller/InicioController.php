<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ClientesRepository;
use App\Repository\UsuariosRepository;
use DateTime;

class InicioController extends AbstractController
{
    private ClientesRepository $clientesRepository;

    public function __construct(ClientesRepository $clientesRepository)
    {
        $this->clientesRepository = $clientesRepository;
    }

    #[Route('/inicio', name: 'app_inicio')]
    #[IsGranted('ROLE_USER')]
    public function index(UsuariosRepository $usuariosRepository, Request $request): Response
    {
        $startDateParam = $request->query->get('start');
        $endDateParam = $request->query->get('end');

        // Si no se proporciona fecha de inicio, establecer desde inicio de año
        $startDate = $startDateParam ? new DateTime($startDateParam) : new DateTime("2024-01-01");
        // Si no se proporciona fecha de fin, usar la fecha actual
        $endDate = $endDateParam ? new DateTime($endDateParam) : new DateTime();

        //uusario logueado
        $usuario = $this->getUser();
        $usuarios = $usuariosRepository->findProyectosCompartidos($usuario, $startDate, $endDate);

        $labels = [];
        $dataBaja = [];
        $dataMedia = [];
        $dataAlta = [];

        // Itera cada usuario y añade al array
        foreach ($usuarios as $usuarioCompartido) {
        $labels[] = $usuarioCompartido->getNombre();
        $baja = $media = $alta = 0;

        // Acceder a los proyectos a través de UsuariosProyectos
        $proyectos = $usuarioCompartido->getUsuariosProyectos()->map(function($up) {
        return $up->getProyecto();
        })->toArray();

        // Contar número de tareas según prioridad y verificar que pertenezcan a los proyectos compartidos
        foreach ($usuarioCompartido->getTareas() as $tarea) {
            if (in_array($tarea->getProyecto(), $proyectos)) {
                switch ($tarea->getPrioridad()) {
                    case 'BAJA':
                        $baja++;
                        break;
                    case 'MEDIA':
                        $media++;
                        break;
                    case 'ALTA':
                        $alta++;
                        break;
                }
            }
        }

        $dataBaja[] = $baja;
        $dataMedia[] = $media;
        $dataAlta[] = $alta;
            }

        // Obtener todos los clientes desde el repositorio
        $clientes = $this->clientesRepository->findAll();
        // Obtener el total de clientes
        $totalClientes = $this->clientesRepository->count([]);

        // Preparar los datos para el gráfico de clientes
        $datosClientes = [];
        foreach ($clientes as $cliente) {
            // Contar la cantidad de proyectos asociados a cada cliente
            $cantidadProyectos = count($cliente->getProyectos());

            // Agregar los datos al array
            $datosClientes[] = [
                'nombre' => $cliente->getNombre(),
                'cantidad' => $cantidadProyectos,
            ];
        }

        return $this->render('inicio/index.html.twig', [
            'labels' => json_encode($labels),
            'dataBaja' => json_encode($dataBaja),
            'dataMedia' => json_encode($dataMedia),
            'dataAlta' => json_encode($dataAlta),
            'clientes' => $datosClientes,
            'totalClientes' => $totalClientes,
            'startDateParam' => $startDateParam,  // Pasar los parámetros de fecha a la vista
            'endDateParam' => $endDateParam
        ]);
    }
}