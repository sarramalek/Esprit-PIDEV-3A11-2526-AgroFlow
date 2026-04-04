<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur', name: 'agri_')]
#[IsGranted('ROLE_AGRICULTEUR')]
class AgriculteurDashboardController extends AbstractController
{
    // ── Dashboard ────────────────────────────────────────────────────────────

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('agriculteur_dashboard.html.twig', [
            'animaux'        => 0,
            'terrains'       => 0,
            'machines'       => 0,
            'taches'         => 0,
            'abonnement'     => null,
            'participations' => [],
        ]);
    }

    // ── Animaux ──────────────────────────────────────────────────────────────

    #[Route('/animaux', name: 'animaux')]
    public function animaux(): Response
    {
        return new Response('Module Animaux — à implémenter');
    }

    #[Route('/animaux/examens', name: 'examens')]
    public function examens(): Response
    {
        return new Response('Module Examens vétérinaires — à implémenter');
    }

    // ── Stocks ───────────────────────────────────────────────────────────────

    #[Route('/stocks/categories', name: 'categories')]
    public function categories(): Response
    {
        return new Response('Module Catégories — à implémenter');
    }

    #[Route('/stocks/produits', name: 'produits')]
    public function produits(): Response
    {
        return new Response('Module Produits — à implémenter');
    }

    // ── Terrains ─────────────────────────────────────────────────────────────

    #[Route('/terrains', name: 'terrains')]
    public function terrains(): Response
    {
        return new Response('Module Terrains — à implémenter');
    }

    #[Route('/terrains/rotations', name: 'rotations')]
    public function rotations(): Response
    {
        return new Response('Module Rotations de culture — à implémenter');
    }

    // ── Matériels ────────────────────────────────────────────────────────────

    #[Route('/materiels/machines', name: 'machines')]
    public function machines(): Response
    {
        return new Response('Module Machines — à implémenter');
    }

    #[Route('/materiels/maintenances', name: 'maintenances')]
    public function maintenances(): Response
    {
        return new Response('Module Maintenances — à implémenter');
    }

    // ── Événements ───────────────────────────────────────────────────────────

    #[Route('/evenements', name: 'evenements')]
    public function evenements(): Response
    {
        return new Response('Module Événements — à implémenter');
    }

    #[Route('/evenements/participations', name: 'participations')]
    public function participations(): Response
    {
        return new Response('Module Participations — à implémenter');
    }

    // ── Abonnements ──────────────────────────────────────────────────────────

    #[Route('/abonnements/offres', name: 'offres')]
    public function offres(): Response
    {
        return new Response('Module Offres — à implémenter');
    }

    #[Route('/abonnements', name: 'abonnements')]
    public function abonnements(): Response
    {
        return new Response('Module Abonnements — à implémenter');
    }

    // ── Tâches ───────────────────────────────────────────────────────────────

    // ── Tâches ───────────────────────────────────────────────────────────────

#[Route('/taches', name: 'taches')]
public function taches(): Response
{
    return $this->redirectToRoute('app_tache_front');
}
}
