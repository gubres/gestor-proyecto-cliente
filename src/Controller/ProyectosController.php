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

    private $proyectosRepository;

    public function __construct(ProyectosRepository $proyectosRepository)
    {
        $this->proyectosRepository = $proyectosRepository;
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
        $id = $request->attributes->get('id');
        $proyecto = $this->proyectosRepository->find($id);

        if (!$proyecto) {
            throw $this->createNotFoundException('Proyecto no encontrado');
        }

        if ($this->isCsrfTokenValid('delete'.$proyecto->getId(), $request->request->get('_token'))) {
            $entityManager->remove($proyecto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_proyectos_index');
    }

    #[Route('/eliminarproyectos', name: 'eliminar_registros', methods: ['POST'])]
    public function eliminarProyectos(Request $request, Proyectos $proyecto, EntityManagerInterface $entityManager)
    {
        $id = $request->request->get('id'); 
        $proyecto = $this->proyectosRepository->find($id);

        if ($proyecto) { // Corregir aquí
            $entityManager->remove($proyecto);
            $entityManager->flush();
        }

        // Devuelve una respuesta HTTP 200 indicando éxito
        return new Response('Proyectos eliminados correctamente', Response::HTTP_OK);
        

        // En caso de error, devuelve una respuesta HTTP 400
        return new Response('No se proporcionó un ID de proyecto válido.', Response::HTTP_BAD_REQUEST);
    
        return $this->redirectToRoute('app_proyectos_index');
    

    }
}
