<?php

namespace App\Controller\User;

use App\Entity\User\Abonnement;
use App\Form\User\AbonnementType;
use App\Repository\User\AbonnementRepository;
use App\Repository\User\OffreRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
#[Route('/admin/abonnements', name: 'admin_abonnements')]
class AbonnementController extends AbstractController
{
    // ── LIST ─────────────────────────────────────────────────────────────────
    #[Route('', name: '_index', methods: ['GET'])]
    public function index(AbonnementRepository $repo): Response
    {
        return $this->render('User/listAbonn.html.twig', [
            'abonnements' => $repo->findAll(),
        ]);
    }
// ── EXPORT PDF LISTE ─────────────────────────────────────────────────────
    #[Route('/export/pdf', name: '_export_pdf', methods: ['GET'])]
    public function exportPdf(AbonnementRepository $repo, OffreRepository $offreRepo): Response
    {
        $abonnements = $repo->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $rows = '';
        foreach ($abonnements as $i => $a) {
            $offre = $offreRepo->find($a->getIdOffre());
            $situColor = match(strtolower($a->getSituation())) {
                'actif'   => '#065F46',
                'inactif' => '#B45309',
                default   => '#DC2626',
            };
            $situBg = match(strtolower($a->getSituation())) {
                'actif'   => '#D1FAE5',
                'inactif' => '#FEF3C7',
                default   => '#FEE2E2',
            };
            $enRetard = $a->getDateExpiration() < new \DateTime() && strtolower($a->getSituation()) === 'actif';

            $rows .= '
            <tr style="background:' . ($i % 2 === 0 ? '#fff' : '#f9f9f9') . ';">
                <td>#' . $a->getIdAbonn() . '</td>
                <td>' . $a->getCin() . '</td>
                <td>' . htmlspecialchars($offre?->getNomOffre() ?? '—') . '</td>
                <td>' . $a->getDateInscription()->format('d/m/Y') . '</td>
                <td style="' . ($enRetard ? 'color:#DC2626; font-weight:700;' : '') . '">' . $a->getDateExpiration()->format('d/m/Y') . ($enRetard ? ' ⚠' : '') . '</td>
                <td><span style="background:' . $situBg . '; color:' . $situColor . '; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700;">' . ucfirst($a->getSituation()) . '</span></td>
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
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th { background: #2D5A27; color: white; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
                td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
                .footer { margin-top: 20px; padding: 12px 30px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 10px; color: #aaa; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">AGRO<span>FLOW</span></div>
                <h1>Liste des Abonnements</h1>
                <p>Générée le ' . (new \DateTime())->format('d/m/Y à H:i') . '</p>
            </div>
            <div class="content">
                <p class="total">Total : <strong>' . count($abonnements) . ' abonnement(s)</strong></p>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>CIN</th>
                            <th>Offre</th>
                            <th>Inscription</th>
                            <th>Expiration</th>
                            <th>Situation</th>
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
                'Content-Disposition' => 'attachment; filename="abonnements-' . date('Y-m-d') . '.pdf"',
            ]
        );
    }

    // ── CREATE ────────────────────────────────────────────────────────────────
    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $abonnement = new Abonnement();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($abonnement);
            $em->flush();
            $this->addFlash('success', 'Abonnement créé avec succès.');
            return $this->redirectToRoute('admin_abonnements_index');
        }

        return $this->render('User/newAbonn.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────
    #[Route('/{idAbonn}', name: '_show', methods: ['GET'])]
    public function show(Abonnement $abonnement): Response
    {
        return $this->render('User/showAbonn.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }
// ── PDF UN ABONNEMENT ─────────────────────────────────────────────────────
    #[Route('/{idAbonn}/pdf', name: '_pdf', methods: ['GET'])]
    public function pdf(Abonnement $abonnement, OffreRepository $offreRepo): Response
    {
        $offre = $offreRepo->find($abonnement->getIdOffre());

        $situColor = match(strtolower($abonnement->getSituation())) {
            'actif'   => '#065F46',
            'inactif' => '#B45309',
            default   => '#DC2626',
        };

        $enRetard = $abonnement->getDateExpiration() < new \DateTime()
                    && strtolower($abonnement->getSituation()) === 'actif';

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
                .section-title { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin: 20px 0 10px; border-bottom: 1px solid #eee; padding-bottom: 6px; }
                .row { display: flex; justify-content: space-between; margin-bottom: 10px; }
                .label { color: #888; font-size: 13px; }
                .value { font-weight: 600; font-size: 13px; color: #2C3E50; }
                .badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; color: white; background: ' . $situColor . '; }
                .retard { color: #DC2626; font-weight: 700; }
                .footer { margin-top: 40px; padding: 16px 40px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 11px; color: #aaa; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">AGRO<span>FLOW</span></div>
                <h1>Fiche Abonnement #' . $abonnement->getIdAbonn() . '</h1>
                <p>Générée le ' . (new \DateTime())->format('d/m/Y à H:i') . '</p>
            </div>
            <div class="content">
                <div class="section-title">Informations abonnement</div>
                <div class="row"><span class="label">ID</span><span class="value">#' . $abonnement->getIdAbonn() . '</span></div>
                <div class="row"><span class="label">CIN Agriculteur</span><span class="value">' . $abonnement->getCin() . '</span></div>
                <div class="row"><span class="label">Date inscription</span><span class="value">' . $abonnement->getDateInscription()->format('d/m/Y') . '</span></div>
                <div class="row">
                    <span class="label">Date expiration</span>
                    <span class="value ' . ($enRetard ? 'retard' : '') . '">' . $abonnement->getDateExpiration()->format('d/m/Y') . ($enRetard ? ' ⚠ Expiré' : '') . '</span>
                </div>
                <div class="row"><span class="label">Situation</span><span class="value"><span class="badge">' . ucfirst($abonnement->getSituation()) . '</span></span></div>

                <div class="section-title">Offre souscrite</div>
                <div class="row"><span class="label">Nom offre</span><span class="value">' . htmlspecialchars($offre?->getNomOffre() ?? '—') . '</span></div>
                <div class="row"><span class="label">Description</span><span class="value">' . htmlspecialchars($offre?->getDescription() ?? '—') . '</span></div>
                <div class="row"><span class="label">Prix</span><span class="value">' . number_format($offre?->getPrix() ?? 0, 2) . ' TND</span></div>
                <div class="row"><span class="label">Durée</span><span class="value">' . ($offre?->getDureeOffre() ?? '—') . ' jours</span></div>
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
                'Content-Disposition' => 'attachment; filename="abonnement-' . $abonnement->getIdAbonn() . '.pdf"',
            ]
        );
    }
    // ── EDIT ──────────────────────────────────────────────────────────────────
    #[Route('/{idAbonn}/edit', name: '_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Abonnement $abonnement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Abonnement mis à jour.');
            return $this->redirectToRoute('admin_abonnements_index');
        }

        return $this->render('User/editAbonn.html.twig', [
            'abonnement' => $abonnement,
            'form'       => $form->createView(),
        ]);
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    #[Route('/{idAbonn}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Abonnement $abonnement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $abonnement->getIdAbonn(), $request->request->get('_token'))) {
            $em->remove($abonnement);
            $em->flush();
            $this->addFlash('success', 'Abonnement supprimé.');
        }

        return $this->redirectToRoute('admin_abonnements_index');
    }
}