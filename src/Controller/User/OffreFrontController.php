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
    public function front(OffreRepository $offreRepo, AbonnementRepository $abonnRepo): Response
    {
        /** @var \App\Entity\User\User $user */
        $user        = $this->getUser();
        $cin         = $user->getCin();
        $offres      = $offreRepo->findAll();
        $abonnements = $abonnRepo->findByCin($cin);

        // CINs des offres déjà souscrites et actives
        $offresSouscrites = array_map(fn($a) => $a->getIdOffre(), $abonnements);

        return $this->render('User/FrontOffre.html.twig', [
            'offres'           => $offres,
            'offresSouscrites' => $offresSouscrites,
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