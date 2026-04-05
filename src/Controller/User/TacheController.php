<?php

namespace App\Controller\User;

use App\Entity\User\Tache;
use App\Form\TacheType;
use App\Repository\User\TacheRepository;
use App\Repository\User\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/tache')]
class TacheController extends AbstractController
{
    // ==================== LIST ====================
    #[Route('/', name: 'app_tache_index', methods: ['GET'])]
public function index(Request $request, TacheRepository $tacheRepo): Response
{
    $search    = $request->query->get('q', '');
    $sort      = $request->query->get('sort', 'idTache');
    $direction = $request->query->get('direction', 'ASC');

    // Champs autorisés pour éviter les injections
    $allowedSorts = ['idTache', 'nomTache', 'etat', 'priorite', 'dateEcheancee'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'idTache';
    }
    if (!in_array($direction, ['ASC', 'DESC'])) {
        $direction = 'ASC';
    }

    $taches = $tacheRepo->searchAndSort($search, $sort, $direction);

    return $this->render('User/listTache.html.twig', [
        'taches'       => $taches,
        'searchTerm'   => $search,
        'currentSort'  => $sort,
        'currentDir'   => $direction,
        'stats' => [
            'total'    => count($tacheRepo->findAll()),
            'enCours'  => $tacheRepo->countByEtat('en cours'),
            'terminee' => $tacheRepo->countByEtat('terminée'),
            'enRetard' => count($tacheRepo->findTachesEnRetard()),
        ],
    ]);
}


// ==================== PDF TOUTE LA LISTE ====================
#[Route('/export/pdf', name: 'app_tache_export_pdf', methods: ['GET'])]
public function exportPdf(TacheRepository $tacheRepo): Response
{
    $taches = $tacheRepo->findAll();

    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);

    $rows = '';
    foreach ($taches as $t) {
        $enRetard = $t->getDateEcheancee()
                    && $t->getDateEcheancee() < new \DateTime()
                    && $t->getEtat() !== 'terminée';

        $etatColor = match($t->getEtat()) {
            'terminée' => '#3B6D11',
            'en cours' => '#185FA5',
            'annulée'  => '#A32D2D',
            default    => '#666',
        };

        $prioriteColor = match($t->getPriorite()) {
            'haute'   => '#A32D2D',
            'moyenne' => '#854F0B',
            'basse'   => '#185FA5',
            default   => '#666',
        };

        $rows .= '
        <tr style="background:' . (array_search($t, $taches) % 2 === 0 ? '#fff' : '#f9f9f9') . ';">
            <td>#' . $t->getIdTache() . '</td>
            <td>' . htmlspecialchars($t->getNomTache() ?? '—') . '</td>
            <td>' . ($t->getAssignee() ? htmlspecialchars($t->getAssignee()->getPrenom() . ' ' . $t->getAssignee()->getNom()) : '—') . '</td>
            <td><span style="background:' . $etatColor . '; color:white; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700;">' . ucfirst($t->getEtat() ?? '—') . '</span></td>
            <td><span style="background:' . $prioriteColor . '; color:white; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700;">' . ucfirst($t->getPriorite() ?? '—') . '</span></td>
            <td style="' . ($enRetard ? 'color:#A32D2D; font-weight:700;' : '') . '">' . ($t->getDateEcheancee() ? $t->getDateEcheancee()->format('d/m/Y') : '—') . ($enRetard ? ' ⚠' : '') . '</td>
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
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background: #2D5A27; color: white; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
            td { padding: 8px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
            .footer { margin-top: 20px; padding: 12px 30px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 10px; color: #aaa; text-align: center; }
            .total { font-size: 12px; color: #666; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo">AGRO<span>FLOW</span></div>
            <h1>Liste des Tâches</h1>
            <p>Générée le ' . (new \DateTime())->format('d/m/Y à H:i') . '</p>
        </div>
        <div class="content">
            <p class="total">Total : <strong>' . count($taches) . ' tâche(s)</strong></p>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Assigné à</th>
                        <th>État</th>
                        <th>Priorité</th>
                        <th>Échéance</th>
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
            'Content-Disposition' => 'attachment; filename="taches-' . date('Y-m-d') . '.pdf"',
        ]
    );
}
// ==================== NEW ====================
#[Route('/new', name: 'app_tache_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
{
    $tache = new Tache();

    // Récupérer le user depuis l'URL
    $assigneeId = $request->query->get('assignee');
    if ($assigneeId) {
        $user = $userRepository->find($assigneeId);
        if ($user) {
            $tache->setAssignee($user);
        }
    }

    $form = $this->createForm(TacheType::class, $tache);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($tache);
        $entityManager->flush();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_tache_index');
        } elseif ($this->isGranted('ROLE_AGRICULTEUR')) {
            return $this->redirectToRoute('app_tache_front');
        } else {
            return $this->redirectToRoute('app_tache_index');
        }
    }

    return $this->render('User/newTache.html.twig', [
        'form'  => $form->createView(),
        'tache' => $tache,
    ]);
}
// ==================== SHOW ====================
#[Route('/{idTache}', name: 'app_tache_show', methods: ['GET'])]
public function show(Tache $tache): Response
{
    return $this->render('User/showTache.html.twig', [
        'tache' => $tache,
    ]);
}
// ==================== PDF UNE TACHE ====================
#[Route('/{idTache}/pdf', name: 'app_tache_pdf', methods: ['GET'])]
public function pdf(int $idTache, TacheRepository $tacheRepo): Response
{
    $tache = $tacheRepo->find($idTache);
    if (!$tache) {
        throw $this->createNotFoundException('Tâche non trouvée');
    }

    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);

    $prioriteColor = match($tache->getPriorite()) {
        'haute'   => '#A32D2D',
        'moyenne' => '#854F0B',
        'basse'   => '#185FA5',
        default   => '#666',
    };

    $etatColor = match($tache->getEtat()) {
        'terminée' => '#3B6D11',
        'en cours' => '#185FA5',
        'annulée'  => '#A32D2D',
        default    => '#666',
    };

    $assignee  = $tache->getAssignee();
    $echeance  = $tache->getDateEcheancee()?->format('d/m/Y') ?? '—';
    $enRetard  = $tache->getDateEcheancee()
                 && $tache->getDateEcheancee() < new \DateTime()
                 && $tache->getEtat() !== 'terminée';

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
            .badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; color: white; }
            .retard { color: #A32D2D; font-weight: 700; }
            .footer { margin-top: 40px; padding: 16px 40px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 11px; color: #aaa; text-align: center; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo">AGRO<span>FLOW</span></div>
            <h1>Fiche Tâche #' . $tache->getIdTache() . '</h1>
            <p>Générée le ' . (new \DateTime())->format('d/m/Y à H:i') . '</p>
        </div>
        <div class="content">
            <div class="section-title">Informations générales</div>
            <div class="row"><span class="label">Nom</span><span class="value">' . htmlspecialchars($tache->getNomTache() ?? '—') . '</span></div>
            <div class="row"><span class="label">Description</span><span class="value">' . htmlspecialchars($tache->getDescription() ?? '—') . '</span></div>

            <div class="section-title">Statut</div>
            <div class="row">
                <span class="label">État</span>
                <span class="value"><span class="badge" style="background:' . $etatColor . ';">' . ucfirst($tache->getEtat() ?? '—') . '</span></span>
            </div>
            <div class="row">
                <span class="label">Priorité</span>
                <span class="value"><span class="badge" style="background:' . $prioriteColor . ';">' . ucfirst($tache->getPriorite() ?? '—') . '</span></span>
            </div>
            <div class="row">
                <span class="label">Date d\'échéance</span>
                <span class="value ' . ($enRetard ? 'retard' : '') . '">' . $echeance . ($enRetard ? ' ⚠ En retard' : '') . '</span>
            </div>

            <div class="section-title">Assignation</div>
            <div class="row">
                <span class="label">Assigné à</span>
                <span class="value">' . ($assignee ? htmlspecialchars($assignee->getPrenom() . ' ' . $assignee->getNom()) : '—') . '</span>
            </div>
            ' . ($assignee ? '
            <div class="row"><span class="label">CIN</span><span class="value">' . $assignee->getCin() . '</span></div>
            <div class="row"><span class="label">Email</span><span class="value">' . htmlspecialchars($assignee->getEmail() ?? '—') . '</span></div>
            ' : '') . '
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
            'Content-Disposition' => 'attachment; filename="tache-' . $tache->getIdTache() . '.pdf"',
        ]
    );
}

// ==================== EDIT ====================
#[Route('/{idTache}/edit', name: 'app_tache_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Tache $tache, EntityManagerInterface $em, UserRepository $userRepository): Response
{
    // Récupérer le user depuis l'URL si fourni
    $assigneeId = $request->query->get('assignee');
    if ($assigneeId) {
        $user = $userRepository->find($assigneeId);
        if ($user) {
            $tache->setAssignee($user);
        }
    }

    $form = $this->createForm(TacheType::class, $tache);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_tache_index');
        } elseif ($this->isGranted('ROLE_AGRICULTEUR')) {
            return $this->redirectToRoute('app_tache_front');
        } else {
            return $this->redirectToRoute('app_tache_index');
        }
    }

    return $this->render('User/editTache.html.twig', [
        'tache' => $tache,
        'form'  => $form->createView(),
    ]);
}

// ==================== DELETE ====================
#[Route('/{idTache}/delete', name: 'app_tache_delete', methods: ['POST'])]
public function delete(Request $request, Tache $tache, EntityManagerInterface $em): Response
{
    if ($this->isCsrfTokenValid('delete' . $tache->getIdTache(), $request->request->get('_token'))) {
        $em->remove($tache);
        $em->flush();
        $this->addFlash('success', 'Tâche supprimée avec succès.');
    }

    if ($this->isGranted('ROLE_ADMIN')) {
        return $this->redirectToRoute('app_tache_index');
    } elseif ($this->isGranted('ROLE_AGRICULTEUR')) {
        return $this->redirectToRoute('app_tache_front');
    } else {
        return $this->redirectToRoute('app_tache_index');
    }
}

    
}