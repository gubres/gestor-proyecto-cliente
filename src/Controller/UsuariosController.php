<?php

namespace App\Controller;

use App\Entity\Usuarios;
use App\Form\UsuariosType;
use App\Repository\UsuariosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/usuarios')]
class UsuariosController extends AbstractController
{
    #[Route('/', name: 'app_usuarios_index', methods: ['GET'])]
    public function index(UsuariosRepository $usuariosRepository): Response
    {
        return $this->render('usuarios/index.html.twig', [
            'usuarios' => $usuariosRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_usuarios_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usuario = new Usuarios();
        $form = $this->createForm(UsuariosType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('app_usuarios_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuarios/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_usuarios_show', methods: ['GET'])]
    public function show(Usuarios $usuario): Response
    {
        return $this->render('usuarios/show.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_usuarios_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UsuariosType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_usuarios_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuarios/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_usuarios_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, UsuariosRepository $usuariosRepository): Response
    {
        $id = $request->attributes->get('id');
        $usuario = $usuariosRepository->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
            $entityManager->remove($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_usuarios_index');
    }

        #[Route('/eliminarusuarios', name: 'eliminar_usuarios', methods: ['POST'])]
        public function eliminarUsuarios(Request $request, EntityManagerInterface $entityManager, UsuariosRepository $usuariosRepository)
    {
        $usuariosSeleccionados = $request->request->get('usuarios_seleccionados');

        // Ir uno a uno sobre los IDs de los usuarios y eliminarlos uno por uno
        foreach ($usuariosSeleccionados as $usuarioId) {
            // Encontrar el usuario por su ID
            $usuario = $usuariosRepository->find($usuarioId);

            // Verificar si el usuario existe
            if (!$usuario) {
                // Si el usuario no existe, lanzar una excepción o manejar el error de alguna manera
                throw $this->createNotFoundException('Usuario no encontrado');
            }

            // Verificar el token CSRF
            if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
                // Eliminar el usuario
                $entityManager->remove($usuario);
                $entityManager->flush();
            }
        }

        // Redirigir de vuelta a la página de índice de usuarios después de eliminar
        return $this->redirectToRoute('app_usuarios_index');
    }
}

