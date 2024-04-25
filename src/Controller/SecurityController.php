<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redireccionar al usuario a la página de inicio si ya está autenticado
        if ($this->getUser()) {
            return $this->redirectToRoute('app_inicio');
        }

        // obtener error de login si lo hubiese
        $error = $authenticationUtils->getLastAuthenticationError();
        $errorMessage = null;
        if ($error instanceof AuthenticationException) {
            $errorMessage = 'Credenciales inválidas. Por favor intenta de nuevo.';
        }
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'errorMessage' => $errorMessage,
        ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {

        throw new \Exception();
    }
}
