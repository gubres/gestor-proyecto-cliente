<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UsuariosRepository;

class InicioController extends AbstractController
{
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
        ]);
    }
}
