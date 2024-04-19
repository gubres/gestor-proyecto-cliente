<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Usuarios;
use App\Form\PasswordResetRequestFormType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\PasswordResetType;



class RecuperaContraseña extends AbstractController
{
    #[Route('/recuperarcontraseña', name: 'recuperar_contraseña', methods: ['GET', 'POST'])]
    public function request(Request $request, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(PasswordResetRequestFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $email = $form->get('email')->getData();
        $usuario = $entityManager->getRepository(Usuarios::class)->findOneBy(['email' => $email]);

        if ($usuario) {
            $token = $tokenGenerator->generateToken();
            $usuario->setResetToken($token);
            $entityManager->flush();

            $resetUrl = $this->generateUrl('resetear_contraseña', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            
            $email = (new TemplatedEmail())
                ->from('no-reply@example.com')
                ->to($usuario->getEmail())
                ->subject('Solicitud de restablecimiento de contraseña')
                ->htmlTemplate('emails/reset_password.html.twig')
                ->context(['resetUrl' => $resetUrl]);

            $mailer->send($email);

            $this->addFlash('success', 'Se ha enviado a su dirección de correo electrónico un enlace para restablecer su contraseña.');

            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('error', 'No se ha encontrado ninguna cuenta para este correo electrónico.');
    }

    return $this->render('security/forgot_password.html.twig', [
        'requestForm' => $form->createView(),
    ]);
}

    
    #[Route('/resetearcontraseña/{token}', name: 'resetear_contraseña', methods: ['GET', 'POST'])]
    public function reset(Request $request, string $token, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
{
    $usuario = $entityManager->getRepository(Usuarios::class)->findOneBy(['resetToken' => $token]);

    if (!$usuario) {
        $this->addFlash('error', 'Token inválido');
        return $this->redirectToRoute('app_login');
    }

    $form = $this->createForm(PasswordResetType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $newPassword = $form->get('newPassword')->getData();
        $usuario->setPassword($passwordHasher->hashPassword($usuario, $newPassword));
        $usuario->setResetToken(null);
        $entityManager->flush();

        $this->addFlash('success', 'Su contraseña se ha restablecido correctamente.');

        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/reset_password.html.twig', [
        'resetForm' => $form->createView(),
    ]);
}

}