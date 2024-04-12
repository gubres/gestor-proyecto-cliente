<?php

namespace App\Controller;

use App\Entity\Proyectos;
use App\Entity\UsuariosProyectos;
use App\Form\ProyectosType;
use App\Repository\ProyectosRepository;
use App\Repository\UsuariosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/proyectos')]
class ProyectosController extends AbstractController
{
    private $entityManager;
    private $usuariosRepository;

    public function __construct(EntityManagerInterface $entityManager, UsuariosRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->usuariosRepository = $userRepository;
    }
    
    #[Route('/', name: 'app_proyectos_index', methods: ['GET'])]
    public function index(ProyectosRepository $proyectosRepository, EntityManagerInterface $entityManager): Response
    {
        $proyectos = $entityManager->getRepository(Proyectos::class)->findAll();
        
        // Verifica si hay proyectos, si no, crea un array vacío
        if (empty($proyectos)) {
            $proyectos = [];
        }

        return $this->render('proyectos/index.html.twig', [
            'pageName' => 'Proyectos',
            'proyectos' => $proyectos,
        ]);
    }
   

    #[Route('/new', name: 'app_proyectos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {        
        $proyecto = new Proyectos();
        $form = $this->createForm(ProyectosType::class, $proyecto);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
             // Asignar los usuarios seleccionados al proyecto
             $usuariosSeleccionados = $form->get('usuarios')->getData();
             foreach ($usuariosSeleccionados as $usuario) {
                 $usuarioProyecto = new UsuariosProyectos($usuario,$proyecto);
                 $proyecto->addUsuariosProyectos($usuarioProyecto);
             }
             // Guardar el proyecto
             $entityManager->persist($proyecto);
             $entityManager->flush();
            
            return $this->redirectToRoute('app_proyectos_index');
        }
            
        return $this->render('proyectos/new.html.twig', [
            'pageName' => 'Nuevo Proyecto',
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
            // Obtener los usuarios seleccionados del formulario
            $usuariosSeleccionados = $form->get('usuarios')->getData();
    
            // Actualizar la relación usuariosProyectos
            foreach ($proyecto->getUsuariosProyectos() as $usuarioProyecto) {
                $usuario = $usuarioProyecto->getUsuario();
                // Verificar si el usuario está seleccionado
                if (!$usuariosSeleccionados->contains($usuario)) {
                    // Establecer el estado a False y la fecha de baja
                    $usuarioProyecto->setEstado(false);
                    $usuarioProyecto->setFechaBaja(new \DateTime());
                } else {
                    // El usuario está seleccionado, actualizar el estado y eliminar la fecha de baja
                    $usuarioProyecto->setEstado(true);
                    $usuarioProyecto->setFechaBaja(null);
                }
            }
    
            // Agregar nuevos usuarios seleccionados que no están en la relación
            foreach ($usuariosSeleccionados as $usuario) {
                $usuarioProyectoExistente = $proyecto->getUsuariosProyectos()->filter(function($usuarioProyecto) use ($usuario) {
                    return $usuarioProyecto->getUsuario() === $usuario;
                })->first();
                if (!$usuarioProyectoExistente) {
                    // Agregar nuevo usuario
                    $usuarioProyecto = new UsuariosProyectos($usuario, $proyecto);
                    $entityManager->persist($usuarioProyecto);
                    $proyecto->addUsuariosProyectos($usuarioProyecto);
                }
            }
    
            // Persistir el proyecto actualizado
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
