<?php

namespace App\Controller\Animals;

use App\Entity\Aniamls\Examen; // Corrigé
use App\Form\Animals\ExamenType; // Corrigé (vérifie que ton fichier est src/Form/ExamenType.php)
use App\Repository\Animals\ExamenRepository; // Corrigé
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/examen')]
final class ExamensController extends AbstractController
{
    #[Route(name: 'app_examens_index', methods: ['GET'])]
    public function index(ExamenRepository $examenRepository): Response // Corrigé
    {
        return $this->render('examen/index.html.twig', [
            'examens' => $examenRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_examens_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $examen = new Examen(); // Corrigé
        $form = $this->createForm(ExamenType::class, $examen); // Corrigé
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($examen);
            $entityManager->flush();

            return $this->redirectToRoute('app_examens_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('examen/new.html.twig', [
            'examen' => $examen,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_examens_show', methods: ['GET'])]
    public function show(Examen $examen): Response // Corrigé
    {
        return $this->render('examen/show.html.twig', [
            'examen' => $examen,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_examens_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Examen $examen, EntityManagerInterface $entityManager): Response // Corrigé
    {
        $form = $this->createForm(ExamenType::class, $examen); // Corrigé
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_examens_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('examen/edit.html.twig', [
            'examen' => $examen,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_examens_delete', methods: ['POST'])]
    public function delete(Request $request, Examen $examen, EntityManagerInterface $entityManager): Response // Corrigé
    {
        if ($this->isCsrfTokenValid('delete'.$examen->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($examen);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_examens_index', [], Response::HTTP_SEE_OTHER);
    }
}