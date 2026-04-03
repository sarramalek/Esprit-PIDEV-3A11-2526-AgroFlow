<?php

namespace App\Controller;

use App\Entity\Animaux;
use App\Form\AnimauxType;
use App\Repository\AnimauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/animaux')]
final class AnimauxController extends AbstractController
{
    #[Route(name: 'app_animaux_index', methods: ['GET'])]
    public function index(AnimauxRepository $animauxRepository): Response
    {
        return $this->render('animaux/index.html.twig', [
            'animauxes' => $animauxRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_animaux_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $animaux = new Animaux();
        $form = $this->createForm(AnimauxType::class, $animaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($animaux);
            $entityManager->flush();

            return $this->redirectToRoute('app_animaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('animaux/new.html.twig', [
            'animaux' => $animaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_animaux_show', methods: ['GET'])]
    public function show(Animaux $animaux): Response
    {
        return $this->render('animaux/show.html.twig', [
            'animaux' => $animaux,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_animaux_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Animaux $animaux, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnimauxType::class, $animaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_animaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('animaux/edit.html.twig', [
            'animaux' => $animaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_animaux_delete', methods: ['POST'])]
    public function delete(Request $request, Animaux $animaux, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$animaux->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($animaux);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_animaux_index', [], Response::HTTP_SEE_OTHER);
    }
}
