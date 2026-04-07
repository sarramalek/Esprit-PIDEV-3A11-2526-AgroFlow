<?php

namespace App\Controller\AdminMateriels;

use App\Entity\Materiels\Maintenance;
use App\Repository\Materiels\MaintenanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/materiels/maintenances', name: 'admin_maintenances_')]
class MaintenanceAdminController extends AbstractController
{
    // ─────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        MaintenanceRepository $repo
    ): Response {
        $search     = $request->query->get('search', '');
        $type       = $request->query->get('type', '');
        $sort       = $request->query->get('sort', 'dateMain');
        $dir        = $request->query->get('dir', 'DESC');
        $coutFilter = $request->query->get('coutFilter', '');

        if ($coutFilter === 'asc') {
            $sort = 'cout'; $dir = 'ASC';
        } elseif ($coutFilter === 'desc') {
            $sort = 'cout'; $dir = 'DESC';
        }

        $maintenances = $repo->search($search, $type, $sort, $dir);
        $types        = array_column($repo->countByTypePanne(), 'type');
        $totalCout    = $repo->getTotalCout();

        return $this->render('admins/maintenances/index.html.twig', [
            'maintenances' => $maintenances,
            'types'        => $types,
            'totalCout'    => $totalCout,
            'search'       => $search,
            'selectedType' => $type,
            'sort'         => $sort,
            'dir'          => $dir,
            'coutFilter'   => $coutFilter,
        ]);
    }

    // ─────────────────────────────────────────────
    // STATISTIQUES — Bar Chart
    // ─────────────────────────────────────────────
    #[Route('/statistiques/bar', name: 'stats_bar', methods: ['GET'])]
    public function statsBar(MaintenanceRepository $repo): Response
    {
        $byType    = $repo->countByTypePanne();
        $coutMonth = $repo->getCoutByMonth();
        $totalCout = $repo->getTotalCout();
        $total     = array_sum(array_column($byType, 'total'));

        return $this->render('admins/maintenances/stats_bar.html.twig', [
            'byType'    => $byType,
            'coutMonth' => $coutMonth,
            'totalCout' => $totalCout,
            'total'     => $total,
        ]);
    }

    // ─────────────────────────────────────────────
    // EXPORT PDF
    // ─────────────────────────────────────────────
    #[Route('/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repo): Response
    {
        $maintenances = $repo->findAllOrderedByDate();
        $totalCout    = $repo->getTotalCout();
        $byType       = $repo->countByTypePanne();

        $html = $this->renderView('admins/maintenances/pdf.html.twig', [
            'maintenances' => $maintenances,
            'totalCout'    => $totalCout,
            'byType'       => $byType,
            'date'         => new \DateTime(),
        ]);

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    // ─────────────────────────────────────────────
    // NEW
    // ─────────────────────────────────────────────
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $maintenance = new Maintenance();

        if ($request->isMethod('POST')) {
            $this->handleForm($request, $maintenance);
            $em->persist($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance ajoutée avec succès.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        return $this->render('admins/maintenances/new.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // ─────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        return $this->render('admins/maintenances/show.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // ─────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        MaintenanceRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        if ($request->isMethod('POST')) {
            $this->handleForm($request, $maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance mise à jour.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        return $this->render('admins/maintenances/edit.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // ─────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        Request $request,
        MaintenanceRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        if ($this->isCsrfTokenValid(
            'delete_maintenance_' . $id,
            $request->request->get('_token')
        )) {
            $em->remove($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance supprimée.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_maintenances_index');
    }

    // ─────────────────────────────────────────────
    // HELPER PRIVÉ — sans idM
    // ─────────────────────────────────────────────
    private function handleForm(Request $request, Maintenance $maintenance): void
    {
        $maintenance->setTypePanne($request->request->get('typePanne', ''));
        $maintenance->setCout((float) $request->request->get('cout', 0));
        $maintenance->setDescription($request->request->get('description'));

        $dateStr = $request->request->get('dateMain');
        $maintenance->setDateMain($dateStr ? new \DateTime($dateStr) : null);
    }
}