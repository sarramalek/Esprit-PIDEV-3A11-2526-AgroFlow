<?php

namespace App\Controller;

use App\Entity\Materiels\Machine;
use App\Form\Materiels\MachineType;
use App\Repository\Materiels\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Repository\User\AbonnementRepository;
use App\Repository\User\OffreRepository;
use App\Repository\User\TacheRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur', name: 'agri_')]
#[IsGranted('ROLE_AGRICULTEUR')]
class AgriculteurDashboardController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        AbonnementRepository $abonnRepo,
        OffreRepository      $offreRepo,
        TacheRepository      $tacheRepo
    ): Response {
        /** @var \App\Entity\User\User $user */
        $user = $this->getUser();
        $cin  = $user->getCin();

        $tousAbonnements = $abonnRepo->findByCin($cin);

        $taches          = $tacheRepo->findByAssignee($user);

        $abonnementsActifs = [];
        foreach ($tousAbonnements as $a) {
            if (
                strtolower($a->getSituation()) === 'actif'
                && $a->getDateExpiration() >= new \DateTime('today')
            ) {
                $offre               = $offreRepo->find($a->getIdOffre());
                $abonnementsActifs[] = [
                    'nomOffre' => $offre?->getNomOffre() ?? '—',
                    'duree'    => $offre?->getDureeOffre() ?? 0,
                ];
            }
        }

        return $this->render('agriculteur_dashboard.html.twig', [
            'animaux'           => 0,
            'terrains'          => 0,
            'machines'          => 0,
            'taches'            => count($taches),
            'abonnement'        => null,
            'abonnementsActifs' => $abonnementsActifs,
            'participations'    => [],
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
    // ── Matériels — Machines (CRUD complet) ──────────────────────────────────

    #[Route('/materiels/machines', name: 'machine_index', methods: ['GET'])]
    public function machineIndex(MachineRepository $repo): Response
    {
        return $this->render('machines/index.html.twig', [
            'machines' => $repo->findAll(),
        ]);
    }

    #[Route('/materiels/machines/new', name: 'machine_new', methods: ['GET', 'POST'])]
    public function machineNew(Request $request, EntityManagerInterface $em): Response
    {
        $machine = new Machine();
        $form    = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($machine);
            $em->flush();
            $this->addFlash('success', 'Machine « ' . $machine->getNom() . ' » ajoutée.');
            return $this->redirectToRoute('agri_machine_index');
        }

        return $this->render('machines/new.html.twig', [
            'form'    => $form,
            'machine' => $machine,
        ]);
    }

    #[Route('/materiels/machines/{id}', name: 'machine_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function machineShow(Machine $machine): Response
    {
        return $this->render('machines/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    #[Route('/materiels/machines/{id}/edit', name: 'machine_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function machineEdit(Request $request, Machine $machine, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Machine « ' . $machine->getNom() . ' » mise à jour.');
            return $this->redirectToRoute('agri_machine_index');
        }

        return $this->render('machines/edit.html.twig', [
            'form'    => $form,
            'machine' => $machine,
        ]);
    }

    #[Route('/materiels/machines/{id}', name: 'machine_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function machineDelete(Request $request, Machine $machine, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $machine->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($machine);
            $em->flush();
            $this->addFlash('success', 'Machine supprimée.');
        }

        return $this->redirectToRoute('agri_machine_index');
    }

    /*#[Route('/materiels/machines', name: 'machines')]
    public function machines(): Response
    {
        return new Response('Module Machines — à implémenter');
    }*/

    #[Route('/materiels/maintenances', name: 'maintenances_index')]
    public function maintenances(): Response
    {
        return new Response('Module Maintenances — à implémenter');
    }

    // ── Événements ───────────────────────────────────────────────────────────



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
        return $this->redirectToRoute('app_offre_front');
    }

    #[Route('/abonnements', name: 'abonnement_front')]
    public function abonnements(): Response
    {
        return $this->redirectToRoute('app_abonnement_front');
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
        return $this->redirectToRoute('app_tache_front');
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
