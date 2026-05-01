<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Machine;
use App\Entity\User\User;
use App\Form\Materiels\MachineType;
use App\Repository\Materiels\MachineRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/agriculteur/materiels/machines', name: 'agri_')]
final class MachineController extends AbstractController
{
    private function getFullUser(UserRepository $userRepo): ?User
    {
        $sessionUser = $this->getUser();
        if (!$sessionUser) {
            return null;
        }
        $user = $userRepo->findOneBy(['email' => $sessionUser->getUserIdentifier()]);
        return $user instanceof User ? $user : null;
    }

    // ── Liste ──────────────────────────────────────────────
    #[Route('', name: 'machines', methods: ['GET'])]
    public function machineIndex(
        Request           $request,
        MachineRepository $repo,
        UserRepository    $userRepo,
        PaginatorInterface $paginator
    ): Response {
        $user = $this->getFullUser($userRepo);

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        // search() retourne un array
        $machines = $repo->search([
            'cin'     => $user->getCin(),
            'search'  => trim((string) $request->query->get('search',  '')),
            'etat'    => trim((string) $request->query->get('etat',    '')),
            'sortBy'  => $request->query->get('sortBy',  'dateAchat'),
            'sortDir' => $request->query->get('sortDir', 'DESC'),
        ]);

        $pagination = $paginator->paginate(
            $machines,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('machines/index.html.twig', [
            'machines' => $pagination,
            'machinesPagination' => $pagination,
        ]);
    }

    // ── Statistiques ─────────────────────────────────────────────────────────
    #[Route('/statistiques', name: 'machine_statistiques', methods: ['GET'])]
    public function machineStatistiques(
        MachineRepository $repo,
        UserRepository $userRepo
    ): Response {
        $user = $this->getFullUser($userRepo);
        
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer toutes les machines de l'utilisateur
        $machines = $repo->findBy(['agriculteur' => $user]);
        
        // Statistiques globales
        $stats = [
            'total' => count($machines),
            'parEtat' => [],
            'parMarque' => [],
            'statsKm' => [
                'totalKm' => 0,
                'avgKm' => 0,
                'maxKm' => 0,
                'minKm' => 0
            ]
        ];
        
        // Calcul des statistiques
        $totalKm = 0;
        $kmList = [];
        
        foreach ($machines as $machine) {
            // Par état
            $etat = $machine->getEtatM();
            if (!isset($stats['parEtat'][$etat])) {
                $stats['parEtat'][$etat] = 0;
            }
            $stats['parEtat'][$etat]++;
            
            // Par marque
            $marque = $machine->getMarque();
            if (!isset($stats['parMarque'][$marque])) {
                $stats['parMarque'][$marque] = 0;
            }
            $stats['parMarque'][$marque]++;
            
            // Kilométrage
            $km = $machine->getKilometrage();
            if ($km !== null) {
                $totalKm += $km;
                $kmList[] = $km;
            }
        }
        
        // Statistiques kilométrage
        if (count($kmList) > 0) {
            $stats['statsKm']['totalKm'] = $totalKm;
            $stats['statsKm']['avgKm'] = $totalKm / count($kmList);
            $stats['statsKm']['maxKm'] = max($kmList);
            $stats['statsKm']['minKm'] = min($kmList);
        }
        
        // Trier les états par ordre décroissant
        arsort($stats['parEtat']);
        
        // Trier les marques par ordre décroissant
        arsort($stats['parMarque']);
        
        // Récupérer les maintenances dépassées
        $maintenancesDepassees = [];
        $maintenancesAVenir = [];
        $today = new \DateTime();
        $thirtyDaysLater = (new \DateTime())->modify('+30 days');
        
        foreach ($machines as $machine) {
            $maintenanceDate = $machine->getProchaineMaintenance();
            if ($maintenanceDate) {
                if ($maintenanceDate < $today) {
                    $maintenancesDepassees[] = $machine;
                } elseif ($maintenanceDate <= $thirtyDaysLater) {
                    $maintenancesAVenir[] = $machine;
                }
            }
        }
        
        // Trier les maintenances par date
        usort($maintenancesDepassees, function($a, $b) {
            return $a->getProchaineMaintenance() <=> $b->getProchaineMaintenance();
        });
        
        usort($maintenancesAVenir, function($a, $b) {
            return $a->getProchaineMaintenance() <=> $b->getProchaineMaintenance();
        });
        
        return $this->render('machines/statistiques.html.twig', [
            'stats' => $stats,
            'etatLabels' => array_keys($stats['parEtat']),
            'etatValues' => array_values($stats['parEtat']),
            'marqueLabels' => array_keys($stats['parMarque']),
            'marqueValues' => array_values($stats['parMarque']),
            'maintenancesDepassees' => $maintenancesDepassees,
            'maintenancesAVenir' => $maintenancesAVenir,
        ]);
    }

    // ── Export PDF ──────────────────────────────────────────────────────────
    #[Route('/export-pdf', name: 'machine_export_pdf', methods: ['GET'])]
    public function exportPDF(
        MachineRepository $repo,
        UserRepository $userRepo,
        Request $request
    ): Response {
        $user = $this->getFullUser($userRepo);
        
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer les machines avec les mêmes filtres que la liste
        $machines = $repo->search([
            'cin'     => $user->getCin(),
            'search'  => trim((string) $request->query->get('search',  '')),
            'etat'    => trim((string) $request->query->get('etat',    '')),
            'sortBy'  => $request->query->get('sortBy',  'dateAchat'),
            'sortDir' => $request->query->get('sortDir', 'DESC'),
        ]);
        
        // Configurer Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);
        
        // Rendre le template PDF
        $html = $this->renderView('machines/export_pdf.html.twig', [
            'machines' => $machines,
            'user' => $user,
            'date' => new \DateTime(),
            'filters' => [
                'search' => $request->query->get('search', ''),
                'etat' => $request->query->get('etat', ''),
            ]
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Générer le nom du fichier
        $filename = 'machines_' . date('Y-m-d_H-i-s') . '.pdf';
        
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    // ── Nouvelle machine ──────────────────────────────────────────────────────
    #[Route('/new', name: 'agri_machine_new', methods: ['GET', 'POST'])]
    public function machineNew(
        Request                $request,
        EntityManagerInterface $em,
        UserRepository         $userRepo
    ): Response {
        $user = $this->getFullUser($userRepo);

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        $machine = new Machine();
        $form    = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machine->setAgriculteur($user);
            $em->persist($machine);
            $em->flush();

            $this->addFlash('success', '✅ Machine « ' . $machine->getNom() . ' » ajoutée.');
            return $this->redirectToRoute('agri_machines');
        }

        return $this->render('machines/new.html.twig', [
            'form'    => $form,
            'machine' => $machine,
        ]);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'agri_machine_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function machineShow(
        Machine $machine,
        UserRepository $userRepo
    ): Response {
        $user = $this->getFullUser($userRepo);
        
        // Vérifier que l'utilisateur est propriétaire de la machine
        if (!$user || $machine->getAgriculteur()->getCin() !== $user->getCin()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette machine.');
            return $this->redirectToRoute('agri_machines');
        }
        
        return $this->render('machines/show.html.twig', [
            'machine'    => $machine,
            'nomAgriculteur' => $machine->getNomAgriculteur(),
        ]);
    }

    // ── Édition ───────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'agri_machine_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function machineEdit(
        Request                $request,
        Machine                $machine,
        EntityManagerInterface $em,
        UserRepository         $userRepo
    ): Response {
        $user = $this->getFullUser($userRepo);
        
        // Vérifier que l'utilisateur est propriétaire de la machine
        if (!$user || $machine->getAgriculteur()->getCin() !== $user->getCin()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette machine.');
            return $this->redirectToRoute('agri_machines');
        }
        
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Machine « ' . $machine->getNom() . ' » mise à jour.');
            return $this->redirectToRoute('agri_machines');
        }

        return $this->render('machines/edit.html.twig', [
            'form'    => $form,
            'machine' => $machine,
        ]);
    }

    // ── Suppression ───────────────────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'agri_machine_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function machineDelete(
        Request                $request,
        Machine                $machine,
        EntityManagerInterface $em,
        UserRepository         $userRepo
    ): Response {
        $user = $this->getFullUser($userRepo);
        
        // Vérifier que l'utilisateur est propriétaire de la machine
        if (!$user || $machine->getAgriculteur()->getCin() !== $user->getCin()) {
            $this->addFlash('error', 'Vous n\'avez pas accès à cette machine.');
            return $this->redirectToRoute('agri_machines');
        }
        
        if ($this->isCsrfTokenValid(
            'delete_machine_' . $machine->getId(),
            $request->getPayload()->getString('_token')
        )) {
            $em->remove($machine);
            $em->flush();
            $this->addFlash('success', '🗑️ Machine supprimée.');
        } else {
            $this->addFlash('error', '❌ Token CSRF invalide.');
        }

        return $this->redirectToRoute('agri_machines');
    }
}