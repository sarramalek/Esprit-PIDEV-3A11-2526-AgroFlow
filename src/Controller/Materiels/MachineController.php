<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Machine;
use App\Form\Materiels\MachineType;
use App\Repository\Materiels\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agriculteur/materiels/machines', name: 'agri_')]
final class MachineController extends AbstractController
{
    // ──────────────────────────────────────────
    // LIST
    // ──────────────────────────────────────────
    #[Route('', name: 'machines', methods: ['GET'])]
    public function index(MachineRepository $machineRepository): Response
    {
        $machines = $machineRepository->findAll();

        return $this->render('machines/index.html.twig', [
            'machines' => $machines,
            'total'    => count($machines),
        ]);
    }

    // ──────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────
    #[Route('/new', name: 'machine_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $machine = new Machine();
        $form    = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($machine);
            $em->flush();
            $this->addFlash('success', '✅ La machine a été ajoutée avec succès !');
            return $this->redirectToRoute('agri_machines', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('machines/new.html.twig', [
            'form'    => $form->createView(),
            'machine' => $machine,
        ]);
    }

    // ──────────────────────────────────────────
    // SEARCH
    // ──────────────────────────────────────────
    #[Route('/search', name: 'machine_search', methods: ['GET'])]
    public function search(Request $request, MachineRepository $machineRepository): Response
    {
        $filters = [
            'search'  => $request->query->get('search'),
            'etat'    => $request->query->get('etat'),
            'sortBy'  => $request->query->get('sortBy', 'nom'),
            'sortDir' => $request->query->get('sortDir', 'ASC'),
        ];

        $machines = $machineRepository->search($filters);

        return $this->render('machines/index.html.twig', [
            'machines' => $machines,
            'total'    => count($machines),
            'filters'  => $filters,
        ]);
    }

    // ──────────────────────────────────────────
    // STATISTIQUES — Page HTML (affichage)
    // ──────────────────────────────────────────
    #[Route('/statistiques', name: 'machine_statistiques', methods: ['GET'])]
    public function statistiques(MachineRepository $machineRepository): Response
    {
        $stats = $machineRepository->getStatistiques();

        // Préparer les données pour Chart.js
        $etatLabels  = array_keys($stats['parEtat']);
        $etatValues  = array_values($stats['parEtat']);
        $marqueLabels = array_keys($stats['parMarque']);
        $marqueValues = array_values($stats['parMarque']);

        return $this->render('machines/statistiques.html.twig', [
            'stats'        => $stats,
            'etatLabels'   => $etatLabels,
            'etatValues'   => $etatValues,
            'marqueLabels' => $marqueLabels,
            'marqueValues' => $marqueValues,
        ]);
    }

    // ──────────────────────────────────────────
    // STATISTIQUES PDF — Export
    // ──────────────────────────────────────────
    #[Route('/statistiques/pdf', name: 'machine_statistiques_pdf', methods: ['GET'])]
    public function statistiquesPdf(MachineRepository $machineRepository): Response
    {
        $stats    = $machineRepository->getStatistiques();
        $machines = $machineRepository->findAll();

        $html = $this->renderView('machines/statistiques_pdf.html.twig', [
            'stats'    => $stats,
            'machines' => $machines,
            'date'     => new \DateTime(),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'statistiques-machines-' . date('Y-m-d') . '.pdf';

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    // ──────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────
    #[Route('/{id}', name: 'machine_show', methods: ['GET'])]
    public function show(
        #[MapEntity(mapping: ['id' => 'id'])] Machine $machine
    ): Response {
        return $this->render('machines/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    // ──────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────
    #[Route('/{id}/edit', name: 'machine_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Machine $machine,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ La machine a été modifiée avec succès !');
            return $this->redirectToRoute('agri_machine_show', [
                'id' => $machine->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('machines/edit.html.twig', [
            'machine' => $machine,
            'form'    => $form->createView(),
        ]);
    }

    // ──────────────────────────────────────────
    // DELETE
    // ──────────────────────────────────────────
    #[Route('/{id}/delete', name: 'machine_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Machine $machine,
        EntityManagerInterface $em
    ): Response {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete_machine_' . $machine->getId(), $token)) {
            $em->remove($machine);
            $em->flush();
            $this->addFlash('success', '🗑️ Machine supprimée avec succès.');
        } else {
            $this->addFlash('error', '❌ Token CSRF invalide. Suppression annulée.');
        }

        return $this->redirectToRoute('agri_machines', [], Response::HTTP_SEE_OTHER);
    }
}