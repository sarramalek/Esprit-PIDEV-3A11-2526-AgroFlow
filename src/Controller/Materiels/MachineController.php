<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Machine;
use App\Entity\User\User;
use App\Form\Materiels\MachineType;
use App\Repository\Materiels\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agriculteur/materiels/machines', name: 'agri_')]
final class MachineController extends AbstractController
{
    #[Route('', name: 'machines', methods: ['GET'])]
    public function machineIndex(Request $request, MachineRepository $repo): Response
    {
        $cin = $request->getSession()->get('agriculteur_cin');

        if (!$cin) {
            $this->addFlash('error', 'Session expirée. Veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login');
        }

        // Récupération explicite des paramètres GET
        $search = $request->query->get('search', '');
        $etat = $request->query->get('etat', '');
        $sortBy = $request->query->get('sortBy', 'dateAchat');
        $sortDir = $request->query->get('sortDir', 'DESC');

        // Construction des filtres
        $filters = [
            'cin'     => (int) $cin,
            'search'  => trim($search),
            'etat'    => trim($etat),
            'sortBy'  => $sortBy,
            'sortDir' => $sortDir,
        ];

        // Récupération des machines avec les filtres
        $machines = $repo->search($filters);

        return $this->render('machines/index.html.twig', [
            'machines' => $machines,
            'search'   => $search,      // Passage au template
            'etat'     => $etat,        // Passage au template
            'sortBy'   => $sortBy,      // Passage au template
            'sortDir'  => $sortDir,     // Passage au template
        ]);
    }

    #[Route('/new', name: 'machine_new', methods: ['GET', 'POST'])]
    public function machineNew(Request $request, EntityManagerInterface $em): Response
    {
        $machine = new Machine();
        $form    = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cin = $request->getSession()->get('agriculteur_cin');
            if ($cin) {
                $user = $em->getRepository(User::class)->find($cin);
                if ($user) {
                    $machine->setAgriculteur($user);
                    $machine->setCin($cin);
                }
            }
            
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

    #[Route('/{id}', name: 'machine_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function machineShow(Machine $machine): Response
    {
        return $this->render('machines/show.html.twig', [
            'machine' => $machine,
            'nomAgriculteur' => $machine->getNomAgriculteur(),
        ]);
    }

    #[Route('/{id}/edit', name: 'machine_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function machineEdit(Request $request, Machine $machine, EntityManagerInterface $em): Response
    {
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

    #[Route('/{id}/delete', name: 'machine_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function machineDelete(Request $request, Machine $machine, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_machine_' . $machine->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($machine);
            $em->flush();
            $this->addFlash('success', '🗑️ Machine supprimée.');
        } else {
            $this->addFlash('error', '❌ Token CSRF invalide.');
        }

        return $this->redirectToRoute('agri_machines');
    }

    #[Route('/statistiques', name: 'machine_statistiques', methods: ['GET'])]
    public function machineStatistiques(MachineRepository $repo): Response
    {
        $stats = $repo->getStatistiques();
        
        $etatLabels = array_keys($stats['parEtat']);
        $etatValues = array_values($stats['parEtat']);
        $marqueLabels = array_keys($stats['parMarque']);
        $marqueValues = array_values($stats['parMarque']);
        
        return $this->render('machines/statistiques.html.twig', [
            'stats' => $stats,
            'etatLabels' => $etatLabels,
            'etatValues' => $etatValues,
            'marqueLabels' => $marqueLabels,
            'marqueValues' => $marqueValues,
        ]);
    }
}