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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\UsuarioEditType;

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

    


    #[Route('/usuarios/{id}/edit', name: 'app_usuarios_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    $form = $this->createForm(UsuarioEditType::class, $usuario);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Si se proporcionó una nueva contraseña, actualízala
        $newPassword = $form->get('password')->getData(); 
        if (!empty($newPassword)) {
            $usuario->setPassword($passwordHasher->hashPassword($usuario, $newPassword));
        }

        // No es necesario llamar a $entityManager->persist($usuario) si el objeto ya está siendo rastreado por Doctrine
        $entityManager->flush();

        
        return $this->redirectToRoute('app_usuarios_index');
    }

    return $this->render('usuarios/edit.html.twig', [
        'usuario' => $usuario,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_usuarios_delete', methods: ['POST'])]
    public function delete(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_usuarios_index', [], Response::HTTP_SEE_OTHER);
    }
}
