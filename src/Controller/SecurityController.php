<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\FormFactoryInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

         // Crear el formulario de registro
         $registrationForm = $this->createForm(RegistrationFormType::class);


        // Redireccionar al usuario a la página de inicio si ya está autenticado
        if ($this->getUser()) {
            return $this->redirectToRoute('app_inicio');
        }

        // obtener error de login si lo hubiese
        $error = $authenticationUtils->getLastAuthenticationError();
        // último nombre de usuario ingresado 
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'registration_form' => $registrationForm->createView(),
        ]);
    }

    
    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
       
        throw new \Exception();
    }
    

}
