<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ClientesRepository;
use App\Repository\UsuariosRepository;

class InicioController extends AbstractController
{
    private ClientesRepository $clientesRepository;

    public function __construct(ClientesRepository $clientesRepository)
    {
        $this->clientesRepository = $clientesRepository;
    }

    #[Route('/inicio', name: 'app_inicio')]
    public function index(UsuariosRepository $usuariosRepository, Request $request): Response
    {
        //obtener todos los usuarios e inicializar los arrays para poder almaxcenar
        $usuarios = $usuariosRepository->findAll();
        $labels = [];
        $dataBaja = [];
        $dataMedia = [];
        $dataAlta = [];

        //itera cada usuario y añadimos al array
        foreach ($usuarios as $usuario) {
            $labels[] = $usuario->getNombre();
            $baja = $media = $alta = 0;
            
            //contar número de tareas según prioridad y switch para clasificarlas
            foreach ($usuario->getTareas() as $tarea) {
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
        ]);
    }
}

