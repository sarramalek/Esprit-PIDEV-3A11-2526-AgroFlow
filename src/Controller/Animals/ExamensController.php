<?php

namespace App\Controller\Animals;

use App\Entity\Animals\Examen;
use App\Form\Animals\ExamenType;
use App\Repository\Animals\ExamenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/examen')]
final class ExamensController extends AbstractController
{
    #[Route(name: 'app_examens_index', methods: ['GET'])]
    #[IsGranted('ROLE_AGRICULTEUR')]
    public function index(Request $request, ExamenRepository $examenRepository): Response
    {
        $user = $this->getUser();
        $searchTerm = $request->query->get('q');
        $sortBy = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'DESC');
        $typeFilter = $request->query->get('type');

        // Admin voit tout, Agriculteur voit les siens
        $filterUser = $this->isGranted('ROLE_ADMIN') ? null : $user;

        $examens = $examenRepository->searchExamen($searchTerm, $sortBy, $direction, $typeFilter, $filterUser);
        
        // Données de fragilité par espèce (pour les dashboard cards)
        $fragilityStats = $user instanceof \App\Entity\User\User ? $examenRepository->getFragilityData($user) : [];

        return $this->render('Animals/examen/index.html.twig', [
            'examens' => $examens,
            'searchTerm' => $searchTerm,
            'currentSort' => $sortBy,
            'currentDirection' => $direction,
            'currentType' => $typeFilter,
            'fragilityStats' => $fragilityStats
        ]);
    }

    #[Route('/stats', name: 'app_examens_stats', methods: ['GET'])]
    public function stats(ExamenRepository $examenRepository): Response
    {
        $user = $this->getUser();
        $filterUser = $this->isGranted('ROLE_ADMIN') ? null : $user;
        $stats = $examenRepository->countByType($filterUser);

        return $this->render('Animals/examen/stats.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/new', name: 'app_examens_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $examen = new Examen();
        $form = $this->createForm(ExamenType::class, $examen, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sécurité : l'animal doit appartenir à l'utilisateur (déjà géré par le filtrage EntityType si possible, mais sécurisé ici)
            if (!$this->isGranted('ROLE_ADMIN')) {
                $animal = $examen->getAnimal();
                if ($animal && $animal->getUser() !== $this->getUser()) {
                    throw $this->createAccessDeniedException("Animal invalide.");
                }
            }

            $entityManager->persist($examen);
            $entityManager->flush();

            return $this->redirectToRoute('app_examens_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Animals/examen/new.html.twig', [
            'examen' => $examen,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_examens_show', methods: ['GET'])]
    public function show(Examen $examen): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $examen->getAnimal()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Accès refusé.");
        }

        return $this->render('Animals/examen/show.html.twig', [
            'examen' => $examen,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_examens_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Examen $examen, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $examen->getAnimal()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Modification interdite.");
        }

        $form = $this->createForm(ExamenType::class, $examen, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_examens_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Animals/examen/edit.html.twig', [
            'examen' => $examen,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_examens_delete', methods: ['POST'])]
    public function delete(Request $request, Examen $examen, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $examen->getAnimal()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Suppression interdite.");
        }

        if ($this->isCsrfTokenValid('delete'.$examen->getId(), $request->request->get('_token'))) {
            $entityManager->remove($examen);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_examens_index', [], Response::HTTP_SEE_OTHER);
    }
}