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

        // Obtener el error de login si lo hubiese
        $error = $authenticationUtils->getLastAuthenticationError();
        $errorMessage = null;
        if ($error instanceof AuthenticationException) {
            $errorMessage = 'Credenciales inválidas. Por favor intenta de nuevo.';
        }

        // Obtener el último nombre de usuario que intentó iniciar sesión
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error_message' => $errorMessage,
        ]);
    }



    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {

        throw new \Exception();
    }
}
