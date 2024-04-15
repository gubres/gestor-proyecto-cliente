<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\ClientesController;
use App\Repository\ClientesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsuariosRepository;

class InicioController extends AbstractController
{

    private ClientesRepository $clientesRepository;

    public function __construct(ClientesRepository $clientesRepository)
    {
        $this->clientesRepository = $clientesRepository;
    }

    #[Route('/inicio', name: 'app_inicio')]

    public function index(UsuariosRepository $usuariosRepository): Response
    {
        $usuarios = $usuariosRepository->findAll();
        $labels = [];
        $data = [];

        foreach ($usuarios as $usuario) {
            $labels[] = $usuario->getNombre();
            $data[] = count($usuario->getTareas());
        }

        return $this->render('inicio/index.html.twig', [
            'labels' => json_encode($labels),
            'data' => json_encode($data),
            'controller_name' => 'InicioController',

     public function index(ClientesRepository $clientesRepository, Request $request): Response
    {
        // Obtener todos los clientes desde el repositorio
        $clientes = $clientesRepository->findAll();
        // Obtener el total de clientes
        $totalClientes = $this->clientesRepository->count([]);


        // Preparar los datos para el gráfico
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
            'clientes' => $datosClientes,
            'totalClientes' => $totalClientes,

        ]);
    }
}
