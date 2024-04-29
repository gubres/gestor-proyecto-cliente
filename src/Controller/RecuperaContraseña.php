<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Usuarios;
use App\Form\PasswordResetRequestFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
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
            ->to($usuario->getEmail())
            ->subject('Restablecer contraseña')
            ->html($this->renderView('security/restrablecer_contraseña.html.twig', [
                'userEmail' => $usuario->getEmail(),
                'confirmationUrl' => $resetUrl,
            ]));
        $mailer->send($email);
        $this->addFlash('success', 'Correo electrónico enviado. Revisa tu bandeja de entrada.');

            
        }else{
            $this->addFlash('error', 'No se ha encontrado ninguna cuenta para este correo electrónico.');
        }

        
    }

    return $this->render('security/forgot_password.html.twig', [
        'requestForm' => $form->createView(),
    ]);
}


#[Route('/resetearcontraseña/{token}', name: 'resetear_contraseña', methods: ['GET', 'POST'])]
public function reset(Request $request, string $token, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
{   
    // Obtener el usuario por el token
    $usuario = $entityManager->getRepository(Usuarios::class)->findOneBy(['resetToken' => $token]);
    $form = $this->createForm(PasswordResetType::class);
    $form->handleRequest($request);

    // Comprobar si el usuario existe
    if (!$usuario) {
        $this->addFlash('error', 'Token inválido');
        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
            'token' => $token
        ]);

    }
    
    if ($form->isSubmitted() && $form->isValid()) {
        $newPassword = $form->get('newPassword')->getData(); // Directamente obteniendo el dato como string
        if (!empty($newPassword)) {  // Verifica directamente si la contraseña no está vacía
            $usuario->setPassword($passwordHasher->hashPassword($usuario, $newPassword));
            $usuario->setResetToken(null);  // Importante eliminar el token una vez usado
            $entityManager->flush();  // Confirmar cambios en la base de datos
    
            $this->addFlash('success', 'Su contraseña se ha restablecido correctamente.');
            return $this->redirectToRoute('app_login');
        } else {
            $this->addFlash('error', 'La nueva contraseña no puede estar vacía.');
        }
    } else {
        // Capturar y mostrar errores de validación
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
    }
    

    return $this->render('security/reset_password.html.twig', [
        'resetForm' => $form->createView(),
        'token' => $token,
    ]);


}

}
    
