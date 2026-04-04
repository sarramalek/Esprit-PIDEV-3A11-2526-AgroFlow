<?php

namespace App\Controller\User;

use App\Repository\User\AbonnementRepository;
use App\Repository\User\OffreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/agriculteur/abonnement')]
class AbonnementFrontController extends AbstractController
{
    #[Route('/front', name: 'app_abonnement_front', methods: ['GET'])]
public function front(AbonnementRepository $abonnRepo, OffreRepository $offreRepo): Response
{
    /** @var \App\Entity\User\User $user */
    $user = $this->getUser();
    $cin  = $user->getCin();

    $abonnements = $abonnRepo->findByCin($cin);

    $data = array_map(function($a) use ($offreRepo) {
        $offre = $offreRepo->find($a->getIdOffre());
        return [
            'id'              => $a->getIdAbonn(),
            'cin'             => $a->getCin(),
            'dateInscription' => $a->getDateInscription()->format('d/m/Y'),
            'dateExpiration'  => $a->getDateExpiration()->format('d/m/Y'),
            'situation'       => $a->getSituation(),
            'expireBientot'   => $a->getDateExpiration() < new \DateTime('+7 days') && $a->getDateExpiration() > new \DateTime(),
            'estExpire'       => $a->getDateExpiration() < new \DateTime(),
            'nomOffre'        => $offre?->getNomOffre() ?? '—',
            'description'     => $offre?->getDescription() ?? '—',
            'prix'            => $offre?->getPrix() ?? 0,
            'duree'           => $offre?->getDureeOffre() ?? 0,
        ];
    }, $abonnements);

    return $this->render('User/FrontAbonnement.html.twig', [
        'abonnements' => $data,
    ]);
}

    // ==================== PDF ====================
    #[Route('/front/pdf/{id}', name: 'app_abonnement_pdf', methods: ['GET'])]
    public function pdf(int $id, AbonnementRepository $abonnRepo, OffreRepository $offreRepo): Response
    {
        $abonnement = $abonnRepo->find($id);

        if (!$abonnement) {
            throw $this->createNotFoundException('Abonnement non trouvé');
        }

        // Sécurité : l'agriculteur ne peut télécharger que ses propres abonnements
        $user = $this->getUser();
        if ($abonnement->getCin() !== $user->getCin()) {
            throw $this->createAccessDeniedException();
        }

        $offre = $offreRepo->find($abonnement->getIdOffre());

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $situation  = $abonnement->getSituation();
        $situColor  = match($situation) {
            'actif'    => '#2D5A27',
            'expiré'   => '#A32D2D',
            'suspendu' => '#854F0B',
            default    => '#666',
        };

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; color: #2C3E50; margin: 0; padding: 0; }
                .header { background: #2D5A27; color: white; padding: 30px 40px; }
                .header h1 { margin: 0; font-size: 26px; }
                .header p { margin: 5px 0 0; font-size: 13px; opacity: 0.8; }
                .content { padding: 30px 40px; }
                .section-title { font-size: 13px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin: 25px 0 12px; border-bottom: 1px solid #eee; padding-bottom: 6px; }
                .row { display: flex; justify-content: space-between; margin-bottom: 10px; }
                .label { color: #888; font-size: 13px; }
                .value { font-weight: 600; font-size: 13px; color: #2C3E50; }
                .badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; color: white; background: ' . $situColor . '; }
                .footer { margin-top: 40px; padding: 20px 40px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 11px; color: #aaa; text-align: center; }
                .logo-text { font-size: 20px; font-weight: 900; letter-spacing: 2px; }
                .logo-text span { color: #a8d5a2; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-text">AGRO<span>FLOW</span></div>
                <h1>Reçu d\'Abonnement</h1>
                <p>Généré le ' . (new \DateTime())->format('d/m/Y à H:i') . '</p>
            </div>
            <div class="content">
                <div class="section-title">Informations Abonnement</div>
                <div class="row"><span class="label">Numéro</span><span class="value">#' . $abonnement->getIdAbonn() . '</span></div>
                <div class="row"><span class="label">CIN Agriculteur</span><span class="value">' . $abonnement->getCin() . '</span></div>
                <div class="row"><span class="label">Date d\'inscription</span><span class="value">' . $abonnement->getDateInscription()->format('d/m/Y') . '</span></div>
                <div class="row"><span class="label">Date d\'expiration</span><span class="value">' . $abonnement->getDateExpiration()->format('d/m/Y') . '</span></div>
                <div class="row"><span class="label">Situation</span><span class="value"><span class="badge">' . ucfirst($situation) . '</span></span></div>

                <div class="section-title">Offre Souscrite</div>
                <div class="row"><span class="label">Nom de l\'offre</span><span class="value">' . ($offre?->getNomOffre() ?? '—') . '</span></div>
                <div class="row"><span class="label">Description</span><span class="value">' . ($offre?->getDescription() ?? '—') . '</span></div>
                <div class="row"><span class="label">Prix</span><span class="value">' . number_format($offre?->getPrix() ?? 0, 2) . ' TND</span></div>
                <div class="row"><span class="label">Durée</span><span class="value">' . ($offre?->getDureeOffre() ?? '—') . ' jours</span></div>
            </div>
            <div class="footer">
                AgroFlow &mdash; Plateforme de gestion agricole &mdash; Document généré automatiquement
            </div>
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
}