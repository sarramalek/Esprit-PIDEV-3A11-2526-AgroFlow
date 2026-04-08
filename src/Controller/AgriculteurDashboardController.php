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
            $this->addFlash('success', 'Machine « '.$machine->getNom().' » ajoutée.');
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
            $this->addFlash('success', 'Machine « '.$machine->getNom().' » mise à jour.');
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
        if ($this->isCsrfTokenValid('delete'.$machine->getId(), $request->getPayload()->getString('_token'))) {
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
        return $this->redirectToRoute('app_offre_front');
    }

    #[Route('/abonnements', name: 'abonnements')]
    public function abonnements(): Response
    {
        return $this->redirectToRoute('app_abonnement_front');
    }

    // ── Tâches ───────────────────────────────────────────────────────────────

    #[Route('/taches', name: 'taches')]
    public function taches(): Response
    {
        return $this->redirectToRoute('app_tache_front');
    }
}