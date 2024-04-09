<?php

namespace App\Controller;

use App\Entity\Proyectos;
use App\Entity\UsuariosProyectos;
use App\Form\ProyectosType;
use App\Repository\ProyectosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/proyectos')]
class ProyectosController extends AbstractController
{
    #[Route('/', name: 'app_proyectos_index', methods: ['GET'])]
    public function index(ProyectosRepository $proyectosRepository): Response
    {
        return $this->render('proyectos/index.html.twig', [
            'proyectos' => $proyectosRepository->findAll(),
        ]);
    }
  
    #[Route('/new', name: 'app_proyectos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $proyecto = new Proyectos();
        $form = $this->createForm(ProyectosType::class, $proyecto);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Agregar usuarios seleccionados a la relación usuariosProyectos
            foreach ($form->get('usuarios')->getData() as $usuario) {
                $usuariosProyectos = new UsuariosProyectos($usuario, $proyecto);
                $usuario->addUsuariosProyecto($usuariosProyectos); // Usar addUsuariosProyecto()
                $entityManager->persist($usuariosProyectos);
            }
    
            $entityManager->persist($proyecto);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_usuarios_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('proyectos/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{id}', name: 'app_proyectos_show', methods: ['GET'])]
    public function show(Proyectos $proyecto): Response
    {
        return $this->render('proyectos/show.html.twig', [
            'proyecto' => $proyecto,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_proyectos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Proyectos $proyecto, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProyectosType::class, $proyecto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_proyectos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('proyectos/edit.html.twig', [
            'proyecto' => $proyecto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_proyectos_delete', methods: ['POST'])]
    public function delete(Request $request, Proyectos $proyecto, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$proyecto->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($proyecto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_proyectos_index', [], Response::HTTP_SEE_OTHER);
    }
}
