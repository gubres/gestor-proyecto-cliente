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
use App\Form\RegistrationFormType;
use Symfony\Component\Form\FormError;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;





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
    public function new(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new Usuarios();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailExistente = $entityManager->getRepository(Usuarios::class)->findOneBy(['email' => $user->getEmail()]);
            if ($emailExistente) {
                $form->get('email')->addError(new FormError("Este email ya está registrado."));
                // Renderizar el formulario nuevamente si el email ya existe
                return $this->render('usuarios/new.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $user->setConfirmationToken(rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '='));


            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            $entityManager->persist($user);
            $entityManager->flush(); 

           $confirmationUrl = $this->generateUrl('app_verify_email', [
            'token' => $user->getConfirmationToken()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

            // Mover el envío de correo después de la persistencia y flush para asegurar que el usuario está completamente guardado
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@cesur.com', 'App Mail Bot'))
                ->to($user->getEmail())
                ->subject('Por favor, confirme su email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
                ->context([
                    'userId' => $user->getId(),
                    'confirmationUrl' => $confirmationUrl,
                    
                ]);

            $mailer->send($email);

            

            return $this->redirectToRoute('app_login');
        }

        return $this->render('usuarios/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


     //verificar usuario y marcarlo como verificado. 
     #[Route('/verify/email', name: 'app_verify_email')]
     public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): Response {
        $token = $request->query->get('token');
    
        if ($token) {
            $user = $entityManager->getRepository(Usuarios::class)->findOneBy(['confirmationToken' => $token]);
    
            if ($user) {
                $user->setIsVerified(true);
                $user->setConfirmationToken(null);
                $entityManager->flush();
    
                $this->addFlash('success', 'Tu email ha sido verificado.');
    
                return $this->redirectToRoute('app_usuarios_index'); 
            }
        }
    
        $this->addFlash('error', 'No válido');
        return $this->redirectToRoute('app_usuarios_new');
    }

    


    #[Route('/usuarios/{id}/edit', name: 'app_usuarios_edit', methods: ['GET', 'POST'])]

    public function edit(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UsuarioEditType::class, $usuario);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Comprueba si se modifica la contraseña
            $newPassword = $form->get('newPassword')->getData(); 
            if ($newPassword !== null && $newPassword !== '') {
                // Actualiza la contraseña solo si se ha modificado
                $usuario->setPassword($passwordHasher->hashPassword($usuario, $newPassword));
            }
    
            
            $entityManager->flush();
    
            
            $this->addFlash('success', 'El usuario ha sido actualizado con éxito.');
    
            return $this->redirectToRoute('app_usuarios_index');

        }
    
        return $this->render('usuarios/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_usuarios_delete', methods: ['POST'])]
    public function delete(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager): Response

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


    #[Route('/{id}', name: 'app_usuarios_show', methods: ['GET'])]
    public function show(Usuarios $usuario): Response
    {
        return $this->render('usuarios/show.html.twig', [
            'usuario' => $usuario,
        ]);
    }
    

}

