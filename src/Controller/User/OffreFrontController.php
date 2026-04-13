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
    $user        = $this->getUser();
    $cin         = $user->getCin();
    $toutesOffres = $offreRepo->findAll();
    $abonnements = $abonnRepo->findByCin($cin);

    $offresSouscrites = array_map(fn($a) => $a->getIdOffre(), $abonnements);

    // ── Pagination ──
    $parPage     = 6;
    $page        = max(1, (int) $request->query->get('page', 1));
    $total       = count($toutesOffres);
    $totalPages  = (int) ceil($total / $parPage);
    $page        = min($page, max(1, $totalPages));
    $offres      = array_slice($toutesOffres, ($page - 1) * $parPage, $parPage);
    // ────────────────

    // ── Suggestion ──
    $offreSuggereee   = null;
    $raisonSuggestion = null;

    if (!empty($toutesOffres)) {
        $dateCreation = $user->getDateCreationcpt();
        $anciennete   = $dateCreation
            ? (new \DateTime())->diff($dateCreation)->days
            : 0;

        $scores = [];
        foreach ($toutesOffres as $offre) {
            if (in_array($offre->getIdOffres(), $offresSouscrites)) {
                continue;
            }
            $score = 0;
            $duree = $offre->getDureeOffre() ?? 30;
            $prix  = $offre->getPrix() ?? 1;

            if ($anciennete < 30) {
                $score += max(0, 100 - $duree);
            } elseif ($anciennete < 180) {
                $score += max(0, 100 - abs($duree - 30));
            } else {
                $score += min($duree, 100);
            }

            if (!empty($abonnements)) {
                $dernier       = end($abonnements);
                $derniereOffre = $offreRepo->find($dernier->getIdOffre());
                if ($derniereOffre) {
                    $derniereDuree = $derniereOffre->getDureeOffre() ?? 0;
                    if ($duree >= $derniereDuree) $score += 50;
                }
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

            if ($anciennete < 30) {
                $raisonSuggestion = "En tant que nouvel agriculteur, cette offre courte durée est idéale pour démarrer.";
            } elseif (empty($abonnements)) {
                $raisonSuggestion = "Le meilleur rapport qualité/prix pour votre premier abonnement.";
            } elseif ($anciennete >= 180) {
                $raisonSuggestion = "Fidèle à AgroFlow, cette offre longue durée vous offrira le meilleur suivi.";
            } else {
                $raisonSuggestion = "Basé sur votre historique, cette offre correspond le mieux à vos besoins.";
            }
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
    ]);
}

    #[Route('/souscrire/{id}', name: 'app_offre_souscrire', methods: ['POST'])]
    public function souscrire(int $id, OffreRepository $offreRepo, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User\User $user */
        $user  = $this->getUser();
        $offre = $offreRepo->find($id);

        if (!$offre) {
            throw $this->createNotFoundException('Offre non trouvée');
        }

        $dateDebut = new \DateTime('today');
        $dateFin   = new \DateTime('today');
        $dateFin->modify('+' . $offre->getDureeOffre() . ' days');

        $abonnement = new Abonnement();
        $abonnement->setCin($user->getCin());
        $abonnement->setIdOffre($offre->getIdOffres());
        $abonnement->setDateInscription($dateDebut);
        $abonnement->setDateExpiration($dateFin);
        $abonnement->setSituation('actif');

        $em->persist($abonnement);
        $em->flush();

        $this->addFlash('success', 'Vous êtes maintenant inscrit à l\'offre "' . $offre->getNomOffre() . '".');

        return $this->redirectToRoute('app_abonnement_front');
    }
}