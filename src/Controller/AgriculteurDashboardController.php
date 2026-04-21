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

    #[Route('/terrains/plantes', name: 'plantes')]
    public function plantes(): Response
    {
        return new Response('Module Plantes — à implémenter');
    }

    // ── Matériels ────────────────────────────────────────────────────────────

    #[Route('/materiels/machines', name: 'machines')]
    public function machines(): Response
    {
        return new Response('Module Machines — à implémenter');
    }

    #[Route('/materiels/maintenances', name: 'maintenances_index')]
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

    #[Route('/evenements/participations', name: 'participation_index')]
    public function participations(): Response
    {
        return new Response('Module Participations — à implémenter');
    }

    #[Route('/evenements/index', name: 'evenement_index')]
    public function evenementIndex(): Response
    {
        return new Response('Module Événements — à implémenter');
    }

    // ── Abonnements ──────────────────────────────────────────────────────────

    #[Route('/abonnements/offres', name: 'offre_front')]
    public function offres(): Response
    {
        return new Response('Module Offres — à implémenter');
    }

    #[Route('/abonnements', name: 'abonnement_front')]
    public function abonnements(): Response
    {
        return new Response('Module Abonnements — à implémenter');
    }

    #[Route('/abonnements/pdf', name: 'abonnement_pdf')]
    public function abonnementPdf(): Response
    {
        return new Response('Génération PDF — à implémenter');
    }

    // ── Tâches ───────────────────────────────────────────────────────────────

    #[Route('/taches', name: 'taches')]
    public function taches(): Response
    {
        return new Response('Module Tâches — à implémenter');
    }

    #[Route('/ouvriers', name: 'ouvriers')]
    public function ouvriers(): Response
    {
        return new Response('Module Ouvriers — à implémenter');
    }

    // ── Profil ───────────────────────────────────────────────────────────────
    #[Route('/profile/update', name: 'profile_update', methods: ['POST'])]
    public function profileUpdate(\Symfony\Component\HttpFoundation\Request $request, \Doctrine\ORM\EntityManagerInterface $em, \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher): \Symfony\Component\HttpFoundation\JsonResponse
    {
        /** @var \App\Entity\User\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Non connecté'], 401);
        }

        $email   = $request->request->get('email');
        $nom     = $request->request->get('nom');
        $prenom  = $request->request->get('prenom');
        $tel     = $request->request->get('tel');
        $ville   = $request->request->get('ville');
        $pwd     = $request->request->get('password');

        if ($email)  $user->setEmail($email);
        if ($nom)    $user->setNom($nom);
        if ($prenom) $user->setPrenom($prenom);
        if ($tel)    $user->setTel($tel);
        if ($ville)  $user->setVille($ville);
        
        if (!empty($pwd)) {
            $user->setMdp($passwordHasher->hashPassword($user, $pwd));
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'nom'     => $user->getNom(),
            'prenom'  => $user->getPrenom()
        ]);
    }
}
