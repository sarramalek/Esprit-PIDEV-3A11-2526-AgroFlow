<?php

namespace App\Controller\User;

use App\Entity\User\Abonnement;
use App\Repository\User\AbonnementRepository;
use App\Repository\User\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/offre')]
class OffreFrontController extends AbstractController
{
  #[Route('/front', name: 'app_offre_front', methods: ['GET'])]
public function front(
    Request $request,
    OffreRepository $offreRepo,
    AbonnementRepository $abonnRepo
): Response {
    /** @var \App\Entity\User\User $user */
    $user         = $this->getUser();
    $cin          = $user->getCin();
    $toutesOffres = $offreRepo->findAll();
    $abonnements  = $abonnRepo->findByCin($cin);
    $offresSouscrites = array_map(fn($a) => $a->getIdOffre(), $abonnements);

    // ── Code promo du jour (algorithme déterministe) ──────────
    $today     = new \DateTime('today');
    $seed      = (int) $today->format('Ymd'); // graine = date du jour

    // Génère toujours le même code pour aujourd'hui
    $chars     = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code      = 'AGRO-';
    mt_srand($seed);
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    // Réduction parmi [10, 15, 20, 25, 30] — change chaque jour
    $reductions = [10, 15, 20, 25, 30];
    $reduction  = $reductions[mt_rand(0, count($reductions) - 1)];
    // ─────────────────────────────────────────────────────────

    // ── Pagination ──
    $parPage    = 6;
    $page       = max(1, (int) $request->query->get('page', 1));
    $total      = count($toutesOffres);
    $totalPages = (int) ceil($total / $parPage);
    $page       = min($page, max(1, $totalPages));
    $offres     = array_slice($toutesOffres, ($page - 1) * $parPage, $parPage);

    // ── Suggestion (inchangée) ──
    $offreSuggereee   = null;
    $raisonSuggestion = null;
    if (!empty($toutesOffres)) {
        $dateCreation = $user->getDateCreationcpt();
        $anciennete   = $dateCreation
            ? (new \DateTime())->diff($dateCreation)->days
            : 0;
        $scores = [];
        foreach ($toutesOffres as $offre) {
            if (in_array($offre->getIdOffres(), $offresSouscrites)) continue;
            $score = 0;
            $duree = $offre->getDureeOffre() ?? 30;
            $prix  = $offre->getPrix() ?? 1;
            if ($anciennete < 30)       $score += max(0, 100 - $duree);
            elseif ($anciennete < 180)  $score += max(0, 100 - abs($duree - 30));
            else                        $score += min($duree, 100);
            if (!empty($abonnements)) {
                $dernier       = end($abonnements);
                $derniereOffre = $offreRepo->find($dernier->getIdOffre());
                if ($derniereOffre && $duree >= ($derniereOffre->getDureeOffre() ?? 0)) $score += 50;
            } else {
                $score += max(0, 100 - $prix);
            }
            $score += ($duree / max($prix, 1)) * 10;
            $scores[$offre->getIdOffres()] = $score;
        }
        if (!empty($scores)) {
            arsort($scores);
            $bestId         = array_key_first($scores);
            $offreSuggereee = $offreRepo->find($bestId);
            $raisonSuggestion = $anciennete < 30
                ? "En tant que nouvel agriculteur, cette offre courte durée est idéale pour démarrer."
                : (empty($abonnements)
                    ? "Le meilleur rapport qualité/prix pour votre premier abonnement."
                    : ($anciennete >= 180
                        ? "Fidèle à AgroFlow, cette offre longue durée vous offrira le meilleur suivi."
                        : "Basé sur votre historique, cette offre correspond le mieux à vos besoins."));
        }
    }

    return $this->render('User/FrontOffre.html.twig', [
        'offres'           => $offres,
        'offresSouscrites' => $offresSouscrites,
        'offreSuggereee'   => $offreSuggereee,
        'raisonSuggestion' => $raisonSuggestion,
        'page'             => $page,
        'totalPages'       => $totalPages,
        'total'            => $total,
        'codePromo'        => $code,       // ← ex: "AGRO-K7MN2P"
        'reduction'        => $reduction,  // ← ex: 20
    ]);
}

// ── Souscrire avec code promo ──────────────────────────────────
#[Route('/souscrire/{id}', name: 'app_offre_souscrire', methods: ['POST'])]
public function souscrire(
    int $id,
    Request $request,
    OffreRepository $offreRepo,
    EntityManagerInterface $em
): Response {
    /** @var \App\Entity\User\User $user */
    $user  = $this->getUser();
    $offre = $offreRepo->find($id);
    if (!$offre) throw $this->createNotFoundException('Offre non trouvée');

    $prix      = $offre->getPrix();
    $promoMsg  = '';

    // ── Vérifie le code promo saisi ──
    $codeSaisi = strtoupper(trim($request->request->get('code_promo', '')));

    if ($codeSaisi !== '') {
        // Recalcule le code du jour (même algorithme)
        $today    = new \DateTime('today');
        $seed     = (int) $today->format('Ymd');
        $chars    = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $codeJour = 'AGRO-';
        mt_srand($seed);
        for ($i = 0; $i < 6; $i++) {
            $codeJour .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $reductions = [10, 15, 20, 25, 30];
        $reduction  = $reductions[mt_rand(0, count($reductions) - 1)];

        if ($codeSaisi === $codeJour) {
            $prix     = round($prix * (1 - $reduction / 100), 2);
            $promoMsg = ' avec ' . $reduction . '% de réduction (code promo appliqué)';
        } else {
            $this->addFlash('error', 'Code promo invalide ou expiré.');
        }
    }

    $dateDebut = new \DateTime('today');
    $dateFin   = (new \DateTime('today'))->modify('+' . $offre->getDureeOffre() . ' days');

    $abonnement = new Abonnement();
    $abonnement->setCin($user->getCin());
    $abonnement->setIdOffre($offre->getIdOffres());
    $abonnement->setDateInscription($dateDebut);
    $abonnement->setDateExpiration($dateFin);
    $abonnement->setSituation('actif');
    $abonnement->setPrixPaye((float) $prix);

    $em->persist($abonnement);
    $em->flush();

    $this->addFlash('success',
        'Inscription à "' . $offre->getNomOffre() . '"' . $promoMsg . ' confirmée !'
    );

    return $this->redirectToRoute('app_abonnement_front');
}
}