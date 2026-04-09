<?php

namespace App\Controller\Animals;

use App\Entity\Animals\Animaux;
use App\Form\Animals\AnimauxType;
use App\Repository\Animals\AnimauxRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/animaux')]
final class AnimauxController extends AbstractController
{
    #[Route(name: 'app_animaux_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, AnimauxRepository $animauxRepository): Response
    {
        $user = $this->getUser();
        $searchTerm = $request->query->get('q');
        $sortBy = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'DESC');

        // Admin voit tout, Agriculteur voit les siens
        $filterUser = $this->isGranted('ROLE_ADMIN') ? null : $user;
        
        $animaux = $animauxRepository->searchDashboard($searchTerm, $sortBy, $direction, $filterUser);
        
        // Moyennes globales par espèce pour le calcul de l'IQ (Index Qualité)
        $averages = $animauxRepository->getAverageWeightsBySpecies();

        return $this->render('Animals/animaux/index.html.twig', [
            'animaux' => $animaux,
            'searchTerm' => $searchTerm,
            'currentSort' => $sortBy,
            'currentDirection' => $direction,
            'averages' => $averages
        ]);
    }

    #[Route('/stats', name: 'app_animaux_stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function stats(AnimauxRepository $animauxRepository): Response
    {
        $user = $this->getUser();
        $filterUser = $this->isGranted('ROLE_ADMIN') ? null : $user;
        $stats = $animauxRepository->countByEspece($filterUser);

        return $this->render('Animals/animaux/stats.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/new', name: 'app_animaux_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $animaux = new Animaux();
        $form = $this->createForm(AnimauxType::class, $animaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer l'animal à l'utilisateur connecté
            $animaux->setUser($this->getUser());
            
            $entityManager->persist($animaux);
            $entityManager->flush();

            return $this->redirectToRoute('app_animaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Animals/animaux/new.html.twig', [
            'animaux' => $animaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_animaux_show', methods: ['GET'])]
    public function show(Animaux $animaux): Response
    {
        // Sécurité : l'agriculteur ne peut voir que ses animaux
        if (!$this->isGranted('ROLE_ADMIN') && $animaux->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cet animal.");
        }

        return $this->render('Animals/animaux/show.html.twig', [
            'animaux' => $animaux,
        ]);
    }

    #[Route('/{id}/export/card', name: 'app_animaux_export_card', methods: ['GET'])]
    public function exportCard(Animaux $animaux, PdfService $pdf): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $animaux->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Accès refusé.");
        }

        $html = $this->renderView('Animals/pdf/animal_card.html.twig', [
            'animal' => $animaux
        ]);

        $pdfContent = $pdf->generateBinaryPdf($html);
        
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="identite_'.$animaux->getNom().'.pdf"'
        ]);
    }

    #[Route('/{id}/export/medical', name: 'app_animaux_export_medical', methods: ['GET'])]
    public function exportMedical(Animaux $animaux, PdfService $pdf): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $animaux->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Accès refusé.");
        }

        $html = $this->renderView('Animals/pdf/medical_record.html.twig', [
            'animal' => $animaux
        ]);

        $pdfContent = $pdf->generateBinaryPdf($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="dossier_medical_'.$animaux->getNom().'.pdf"'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_animaux_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Animaux $animaux, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $animaux->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Modification interdite.");
        }

        $form = $this->createForm(AnimauxType::class, $animaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_animaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Animals/animaux/edit.html.twig', [
            'animaux' => $animaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/match', name: 'app_animaux_match', methods: ['GET'])]
    public function match(Animaux $animaux, AnimauxRepository $repository): Response
    {
        // Sécurité : même si c'est une recherche publique, on vérifie que l'utilisateur possède l'animal source
        if (!$this->isGranted('ROLE_ADMIN') && $animaux->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Accès refusé.");
        }

        $partners = $repository->findPotentialPartners($animaux);

        return $this->render('Animals/animaux/match.html.twig', [
            'animal' => $animaux,
            'partners' => $partners,
        ]);
    }

    #[Route('/{id}', name: 'app_animaux_delete', methods: ['POST'])]
    public function delete(Request $request, Animaux $animaux, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $animaux->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Suppression interdite.");
        }

        if ($this->isCsrfTokenValid('delete'.$animaux->getId(), $request->request->get('_token'))) {
            $entityManager->remove($animaux);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_animaux_index', [], Response::HTTP_SEE_OTHER);
    }
}
