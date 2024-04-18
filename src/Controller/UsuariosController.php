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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\UsuarioEditType;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\FormError;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[Route('/usuarios')]
class UsuariosController extends AbstractController
{

    private $usuariosRepository;

    public function __construct(UsuariosRepository $usuariosRepository)
    {
        $this->usuariosRepository = $usuariosRepository;
    }


    #[Route('/', name: 'app_usuarios_index', methods: ['GET'])]
    public function index(UsuariosRepository $usuariosRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
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

            // Codifica y establece la contraseña
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            // Actualiza la propiedad isActive
            $user->setIsActive(true); // Por defecto, el usuario estará activo al registrarse

            // Persiste la entidad
            $entityManager->persist($user);
            $entityManager->flush();

            // Registro exitoso, agregamos un mensaje flash
            $this->addFlash('success', '¡Registro completado! Por favor, inicia sesión.');

            // Llama al método para actualizar los roles basados en la activación del usuario
            $user->updateRolesBasedOnActivation();

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
        $this->denyAccessUnlessGranted('ROLE_USER');
        $form = $this->createForm(UsuarioEditType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Comprueba si se modifica la contraseña y actualiza si es necesario

            // Actualiza la propiedad isActive
            $usuario->setIsActive($form->get('isActive')->getData());

            // Actualiza los roles según el estado de isActive
            if (!$usuario->getIsActive()) {
                // Si el usuario está desactivado, elimina el role ROLE_USER
                $roles = $usuario->getRoles();
                $key = array_search('ROLE_USER', $roles);
                if ($key !== false) {
                    unset($roles[$key]);
                }
                $usuario->setRoles($roles);
            }
            // Aquí llamamos al método para actualizar los roles basados en la activación
            $usuario->updateRolesBasedOnActivation();
            
            // Persiste la entidad actualizada
            $entityManager->persist($usuario);
            $entityManager->flush();

            // Envía mensaje de éxito y redirige
            $this->addFlash('success', 'El usuario ha sido actualizado con éxito.');
            return $this->redirectToRoute('app_usuarios_index');
        }

        // Renderiza el formulario si no se ha enviado o si hay errores
        return $this->render('usuarios/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_usuarios_delete', methods: ['POST'])]
    public function delete(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager): Response

    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $id = $request->attributes->get('id');
        $usuario = $this->usuariosRepository->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
            $entityManager->remove($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_usuarios_index');
    }


    #[Route('/{id}', name: 'app_usuarios_show', methods: ['GET'])]
    public function show(Usuarios $usuario): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('usuarios/show.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    public function success(): Response
    {
        return $this->render('registration/success.html.twig');
    }
    

}

