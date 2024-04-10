<?php

namespace App\Controller;

use App\Entity\Usuarios; 
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 
use Symfony\Component\Form\FormError;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;


class RegistrationController extends AbstractController
{
    

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new Usuarios();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailExistente = $entityManager->getRepository(Usuarios::class)->findOneBy(['email' => $user->getEmail()]);
            if ($emailExistente) {
                $form->get('email')->addError(new FormError("Este email ya está registrado."));
                // Renderizar el formulario nuevamente si el email ya existe
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            $entityManager->persist($user);
            $entityManager->flush();

            // Mover el envío de correo después de la persistencia y flush para asegurar que el usuario está completamente guardado
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@cesur.com', 'App Mail Bot'))
                ->to($user->getEmail())
                ->subject('Por favor, confirme su email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
                ->context([
                    'userId' => $user->getId(),
                ]);

            $mailer->send($email);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    //verificar usuario y marcarlo como verificado. PDT PERSONALIZAR PLANTILLA EMAIL VERIFICACIÓN
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
    $id = $request->query->get('id'); 
    // Buscar el usuario por ID
    $user = $entityManager->getRepository(Usuarios::class)->find($id);

    if (!$user) {
        // caso de usuario no encontrado
        $this->addFlash('error', 'No se ha podido verificar el correo electrónico.');
        return $this->redirectToRoute('app_register');
    }

    // Marcar el usuario como verificado y guardar cambios
    $user->setVerified(true);
    $entityManager->persist($user);
    $entityManager->flush();

    
    $this->addFlash('success', 'Su correo electrónico ha sido verificado con éxito.');
    return $this->redirectToRoute('app_login');
}

}