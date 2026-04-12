<?php

namespace App\Controller\User;

use App\Entity\User\Offre;
use App\Form\User\OffreType;
use App\Repository\User\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/offre')]
class OffreController extends AbstractController
{
    // ==================== LIST ====================
    #[Route('/', name: 'app_offre_list', methods: ['GET'])]
    public function list(Request $request, OffreRepository $offreRepo): Response
    {
        $search    = $request->query->get('q', '');
        $sort      = $request->query->get('sort', 'idOffres');
        $direction = $request->query->get('direction', 'ASC');

        $allowedSorts = ['idOffres', 'nomOffre', 'prix', 'dureeOffre'];
        if (!in_array($sort, $allowedSorts)) $sort = 'idOffres';
        if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'ASC';

        return $this->render('User/listOffre.html.twig', [
            'offres'      => $offreRepo->searchAndSort($search, $sort, $direction),
            'searchTerm'  => $search,
            'currentSort' => $sort,
            'currentDir'  => $direction,
            'stats' => [
                'total'   => $offreRepo->countAll(),
                'avgPrix' => round($offreRepo->avgPrix(), 2),
                'minCher' => $offreRepo->findMoinsCher(1)[0] ?? null,
                'maxLong' => $offreRepo->findPlusLong(1)[0] ?? null,
            ],
        ]);
    }
 // ==================== EXPORT PDF LISTE ====================
    #[Route('/export/pdf', name: 'app_offre_export_pdf', methods: ['GET'])]
    public function exportPdf(OffreRepository $offreRepo): Response
    {
        $offres = $offreRepo->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $rows = '';
        foreach ($offres as $i => $o) {
            $rows .= '
            <tr style="background:' . ($i % 2 === 0 ? '#fff' : '#f9f9f9') . ';">
                <td>#' . $o->getIdOffres() . '</td>
                <td>' . htmlspecialchars($o->getNomOffre() ?? '—') . '</td>
                <td>' . htmlspecialchars($o->getDescription() ? substr($o->getDescription(), 0, 60) . (strlen($o->getDescription()) > 60 ? '...' : '') : '—') . '</td>
                <td>' . ($o->getPrix() ? number_format($o->getPrix(), 2) . ' TND' : '—') . '</td>
                <td>' . ($o->getDureeOffre() ? $o->getDureeOffre() . ' jours' : '—') . '</td>
            </tr>';
        }

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; color: #2C3E50; margin: 0; padding: 0; font-size: 12px; }
                .header { background: #2D5A27; color: white; padding: 20px 30px; }
                .logo { font-size: 18px; font-weight: 900; letter-spacing: 2px; }
                .logo span { color: #a8d5a2; }
                .header h1 { margin: 6px 0 0; font-size: 16px; }
                .header p { margin: 3px 0 0; font-size: 11px; opacity: 0.7; }
                .content { padding: 20px 30px; }
                .total { font-size: 12px; color: #666; margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; }
                th { background: #2D5A27; color: white; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
                td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
                .footer { margin-top: 20px; padding: 12px 30px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 10px; color: #aaa; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">AGRO<span>FLOW</span></div>
                <h1>Liste des Offres</h1>
                <p>Générée le ' . (new \DateTime())->format('d/m/Y à H:i') . '</p>
            </div>
            <div class="content">
                <p class="total">Total : <strong>' . count($offres) . ' offre(s)</strong></p>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Prix</th>
                            <th>Durée</th>
                        </tr>
                    </thead>
                    <tbody>' . $rows . '</tbody>
                </table>
            </div>
            <div class="footer">AgroFlow &mdash; Plateforme de gestion agricole &mdash; Document généré automatiquement</div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="offres-' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    // ==================== NEW ====================
    #[Route('/new', name: 'app_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offre = new Offre();
        $form  = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offre);
            $em->flush();
            $this->addFlash('success', 'Offre créée avec succès.');
            return $this->redirectToRoute('app_offre_list');
        }

        return $this->render('User/newOffre.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ==================== SHOW ====================
    #[Route('/{idOffres}', name: 'app_offre_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        return $this->render('User/showOffre.html.twig', [
            'offre' => $offre,
        ]);
    }
// ==================== PDF UNE OFFRE ====================
    #[Route('/{idOffres}/pdf', name: 'app_offre_pdf', methods: ['GET'])]
    public function pdf(Offre $offre): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; color: #2C3E50; margin: 0; padding: 0; }
                .header { background: #2D5A27; color: white; padding: 30px 40px; }
                .logo { font-size: 22px; font-weight: 900; letter-spacing: 2px; }
                .logo span { color: #a8d5a2; }
                .header h1 { margin: 8px 0 0; font-size: 20px; font-weight: 600; }
                .header p { margin: 4px 0 0; font-size: 12px; opacity: 0.7; }
                .content { padding: 30px 40px; }
                .offre-name { font-size: 26px; font-weight: 700; color: #2C3E50; text-align: center; margin: 20px 0 6px; }
                .offre-id { text-align: center; font-size: 13px; color: #aaa; margin-bottom: 28px; }
                .section-title { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin: 20px 0 10px; border-bottom: 1px solid #eee; padding-bottom: 6px; }
                .row { display: flex; justify-content: space-between; margin-bottom: 12px; }
                .label { color: #888; font-size: 13px; }
                .value { font-weight: 600; font-size: 13px; color: #2C3E50; }
                .prix { font-size: 28px; font-weight: 800; color: #2D5A27; text-align: center; margin: 20px 0; }
                .duration-box { background: #E6F1FB; color: #185FA5; padding: 10px 20px; border-radius: 10px; text-align: center; font-size: 15px; font-weight: 700; margin-bottom: 20px; }
                .footer { margin-top: 40px; padding: 16px 40px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 11px; color: #aaa; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">AGRO<span>FLOW</span></div>
                <h1>Fiche Offre</h1>
                <p>Générée le ' . (new \DateTime())->format('d/m/Y à H:i') . '</p>
            </div>
            <div class="content">
                <div class="offre-name">' . htmlspecialchars($offre->getNomOffre() ?? '—') . '</div>
                <div class="offre-id">Offre #' . $offre->getIdOffres() . '</div>

                <div class="prix">' . ($offre->getPrix() ? number_format($offre->getPrix(), 2) . ' TND' : '—') . '</div>
                <div class="duration-box">
                    <i>Durée : ' . ($offre->getDureeOffre() ? $offre->getDureeOffre() . ' jours' : '—') . '</i>
                </div>

                <div class="section-title">Détails</div>
                <div class="row"><span class="label">Description</span><span class="value">' . htmlspecialchars($offre->getDescription() ?? '—') . '</span></div>
                <div class="row"><span class="label">Prix</span><span class="value">' . ($offre->getPrix() ? number_format($offre->getPrix(), 2) . ' TND' : '—') . '</span></div>
                <div class="row"><span class="label">Durée</span><span class="value">' . ($offre->getDureeOffre() ? $offre->getDureeOffre() . ' jours' : '—') . '</span></div>
            </div>
            <div class="footer">AgroFlow &mdash; Plateforme de gestion agricole &mdash; Document généré automatiquement</div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="offre-' . $offre->getIdOffres() . '.pdf"',
            ]
        );
    }
    // ==================== EDIT ====================
    #[Route('/{idOffres}/edit', name: 'app_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Offre modifiée avec succès.');
            return $this->redirectToRoute('app_offre_list');
        }

        return $this->render('User/editOffre.html.twig', [
            'offre' => $offre,
            'form'  => $form->createView(),
        ]);
    }

    // ==================== DELETE ====================
    #[Route('/{idOffres}/delete', name: 'app_offre_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offre->getIdOffres(), $request->request->get('_token'))) {
            $em->remove($offre);
            $em->flush();
            $this->addFlash('success', 'Offre supprimée avec succès.');
        }
        return $this->redirectToRoute('app_offre_list');
    }
}