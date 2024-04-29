<?php

namespace App\Controller;

use App\Entity\Usuarios;
use App\Repository\UsuariosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


#[Route('/roles')]
#[IsGranted('ROLE_ADMIN')]
class RolesController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UsuariosRepository $usuariosRepository;

    public function __construct(EntityManagerInterface $entityManager, UsuariosRepository $usuariosRepository)
    {
        $this->entityManager = $entityManager;
        $this->usuariosRepository = $usuariosRepository;
    }

    #[Route('/', name: 'user', methods: ['GET'])]
    public function verUser(): Response
    {
        $usuarios = $this->usuariosRepository->findAll();
        return $this->render('roles/index.html.twig', ['usuarios' => $usuarios]);
    }

    #[Route('/data', name: 'user_data', methods: ['GET'])]
    public function userData(): JsonResponse
    {
        $usuarios = $this->usuariosRepository->findAll();
        $data = array_map(function ($usuario) {
            return [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getNombre(),
                'email' => $usuario->getEmail(),
                'estado' => $usuario->getIsActive(),
                'roles' => implode(', ', $usuario->getRoles()),
                'editar' => '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#editModal" data-id="' . $usuario->getId() . '" data-email="' . $usuario->getEmail() . '" data-roles="' . implode(', ', $usuario->getRoles()) . '">Editar</button>',
                'eliminar roles' => '<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="' . $usuario->getId() . '" data-email="' . $usuario->getEmail() . '">Eliminar</button>'
            ];
        }, $usuarios);

        return $this->json(['data' => $data]);
    }


    #[Route('/update', name: 'update_user', methods: ['POST'])]
    public function updateUser(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userId = $request->request->get('userId');
        $newRoles = $request->request->all('roles') ?? [];

        $user = $entityManager->getRepository(Usuarios::class)->find($userId);
        if ($user) {
            $user->setIsActive(true);
            $user->setRoles($newRoles);
            $entityManager->flush();

            $this->addFlash('success', 'Roles actualizados correctamente.');
        } else {
            $this->addFlash('error', 'Usuario no encontrado.');
        }

        return $this->redirectToRoute('user_data');
    }

    #[Route('/delete', name: 'delete_user', methods: ['POST'])]
    public function deleteUser(Request $request): Response
    {
        $userId = $request->request->get('userId');
        $user = $this->usuariosRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Usuario no encontrado.');
            return $this->redirectToRoute('user_data');
        }

        $this->entityManager->beginTransaction(); // iniciar la transacción 
        try {

            $user->setIsActive(false);

            $this->entityManager->flush();
            $this->entityManager->commit(); /*commit de la transacción para garantizar que todas
                                            las modificaciones se produzcan de manera atomica*/

            $this->addFlash('success', 'Usuario ha sido eliminado.');
        } catch (\Exception $e) {
            $this->entityManager->rollback(); // un rollback en la base de datos si en la transacción haya algun error
            $this->addFlash('error', 'Error al eliminar el usuario. Error: ' . $e->getMessage());
        }

        return $this->redirectToRoute('user_data');
    }
}
