<?php

namespace App\Controller;

use App\Entity\Clientes;
use App\Form\ClientesType;
use App\Repository\ClientesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clientes')]
class ClientesController extends AbstractController
{
    private $clientesRepository;

    public function __construct(ClientesRepository $clientesRepository)
    {
        $this->clientesRepository = $clientesRepository;
    }

    #[Route('/', name: 'app_clientes_index', methods: ['GET'])]
    public function index(ClientesRepository $clientesRepository): Response
    {
        return $this->render('clientes/index.html.twig', [
            'clientes' => $clientesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_clientes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cliente = new Clientes();
        $form = $this->createForm(ClientesType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cliente);
            $entityManager->flush();

            return $this->redirectToRoute('app_clientes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clientes/new.html.twig', [
            'cliente' => $cliente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_clientes_show', methods: ['GET'])]
    public function show(Clientes $cliente): Response
    {
        return $this->render('clientes/show.html.twig', [
            'cliente' => $cliente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_clientes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Clientes $cliente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientesType::class, $cliente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_clientes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clientes/edit.html.twig', [
            'cliente' => $cliente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_clientes_delete', methods: ['POST'])]
    public function delete(Request $request, Clientes $cliente, EntityManagerInterface $entityManager): Response
    {
        $id = $request->attributes->get('id');
        $cliente = $this->clientesRepository->find($id);
    
        if (!$cliente) {
            throw $this->createNotFoundException('Cliente no encontrado');
        }
    
        if ($this->isCsrfTokenValid('delete'.$cliente->getId(), $request->request->get('_token'))) {
            // Obtener los proyectos asociados al cliente
            $proyectos = $cliente->getProyectos();
            
            // Cambiar el estado de los proyectos asociados a "Inactivo"
            foreach ($proyectos as $proyecto) {
                $proyecto->setEstado('Inactivo');
                $entityManager->persist($proyecto);
            }
    
            // Eliminar el cliente
            $entityManager->remove($cliente);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('app_clientes_index');
    }


}
