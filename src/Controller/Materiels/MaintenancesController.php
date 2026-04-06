<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Maintenance;
use App\Form\Materiels\MaintenanceType;
use App\Repository\Materiels\MaintenanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/maintenances')]
class MaintenancesController extends AbstractController
{
    // LIST + SEARCH + FILTER + SORT
    #[Route('', name: 'agri_maintenances_index', methods: ['GET'])]
    public function index(Request $request, MaintenanceRepository $repo): Response
    {
        $search  = $request->query->get('search', '');
        $type    = $request->query->get('type', '');
        $sort    = $request->query->get('sort', 'dateMain');
        $dir     = $request->query->get('dir', 'DESC');

        $maintenances = $repo->search($search, $type, $sort, $dir);

        // Stats
        $countByType  = $repo->countByTypePanne();
        $coutByMonth  = $repo->getCoutByMonth();
        $totalCout    = $repo->getTotalCout();

        return $this->render('maintenances/index.html.twig', [
            'maintenances' => $maintenances,
            'search'       => $search,
            'type'         => $type,
            'sort'         => $sort,
            'dir'          => $dir,
            'countByType'  => $countByType,
            'coutByMonth'  => $coutByMonth,
            'totalCout'    => $totalCout,
        ]);
    }

    // EXPORT EXCEL
   #[Route('/export/excel', name: 'agri_maintenances_export_excel', methods: ['GET'])]
public function exportExcel(MaintenanceRepository $repo): StreamedResponse
{
    $maintenances = $repo->findAllOrderedByDate();

    $response = new StreamedResponse(function () use ($maintenances) {
        $handle = fopen('php://output', 'w+');
        // BOM UTF-8
        fwrite($handle, "\xEF\xBB\xBF");

        // Title row
        fputcsv($handle, ['AGROFLOW — Rapport Maintenances'], ';');
        fputcsv($handle, ['Exporté le : ' . (new \DateTime())->format('d/m/Y H:i')], ';');
        fputcsv($handle, ['Nombre d\'enregistrements : ' . count($maintenances)], ';');
        fputcsv($handle, [], ';'); // blank line

        // Header
        fputcsv($handle, ['#', 'Type de panne', 'Coût (DT)', 'Date', 'Description', 'ID Matériel'], ';');

        $index = 1;
        $totalCout = 0.0;
        foreach ($maintenances as $m) {
            fputcsv($handle, [
                $index++,
                $m->getTypePanne(),
                number_format($m->getCout(), 2, ',', ' '),
                $m->getDateMain()?->format('d/m/Y') ?? '',
                $m->getDescription() ?? '',
                $m->getIdM() ? '#' . $m->getIdM() : '',
            ], ';');
            $totalCout += $m->getCout();
        }

        // Total row
        fputcsv($handle, [], ';');
        fputcsv($handle, ['', 'TOTAL', number_format($totalCout, 2, ',', ' ') . ' DT', '', '', ''], ';');

        fclose($handle);
    });

    $filename = 'maintenances_' . (new \DateTime())->format('Ymd_Hi') . '.csv';
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    return $response;
}
    // EXPORT PDF (via simple HTML print)
    #[Route('/export/pdf', name: 'agri_maintenances_export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repo): Response
    {
        $maintenances = $repo->findAllOrderedByDate();
        return $this->render('maintenances/pdf.html.twig', [
            'maintenances' => $maintenances,
        ]);
    }

    // CREATE
    #[Route('/new', name: 'agri_maintenances_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $maintenance = new Maintenance();
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance ajoutée avec succès.');
            return $this->redirectToRoute('agri_maintenances_index');
        }

        return $this->render('maintenances/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // SHOW
    #[Route('/{id}', name: 'agri_maintenances_show', methods: ['GET'])]
    public function show(Maintenance $maintenance): Response
    {
        return $this->render('maintenances/show.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // EDIT
    #[Route('/{id}/edit', name: 'agri_maintenances_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Maintenance $maintenance, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Maintenance mise à jour avec succès.');
            return $this->redirectToRoute('agri_maintenances_show', ['id' => $maintenance->getIdMain()]);
        }

        return $this->render('maintenances/edit.html.twig', [
            'form'        => $form->createView(),
            'maintenance' => $maintenance,
        ]);
    }

    // DELETE
    #[Route('/{id}/delete', name: 'agri_maintenances_delete', methods: ['POST'])]
    public function delete(Request $request, Maintenance $maintenance, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_maintenance_' . $maintenance->getIdMain(), $token)) {
            $em->remove($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('agri_maintenances_index');
    }
}