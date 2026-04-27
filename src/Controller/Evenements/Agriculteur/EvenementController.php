<?php

namespace App\Controller\Evenements\Agriculteur;

use App\Entity\Evenements\Evenement;
use App\Entity\Evenements\Participation;
use App\Entity\User\User;
use App\Form\InscriptionEvenementType;
use App\Repository\Evenements\EvenementRepository;
use App\Repository\Evenements\ParticipationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur/evenements')]
class EvenementController extends AbstractController
{
#[Route('', name: 'agriculteur_evenement_index')]
    public function index(Request $request, EvenementRepository $repo): Response
    {
        $search       = $request->query->get('search', '');
        $dateDebut    = $request->query->get('dateDebut', '');
        $dateFin      = $request->query->get('dateFin', '');
        $lieu         = $request->query->get('lieu', '');
        $statut       = $request->query->get('statut', '');
        $categorieId  = $request->query->get('categorie', '');

        $evenements = $repo->findByFilters($search, $dateDebut, $dateFin, $lieu, $statut, $categorieId ? (int)$categorieId : null);

        return $this->render('Evenements/indexEvenementUser.html.twig', [
            'evenements' => $evenements,
            'total'      => count($evenements),
            'search'     => $search,
            'dateDebutFiltre' => $dateDebut,
            'dateFinFiltre'   => $dateFin,
            'lieuFiltre'      => $lieu,
            'statutFiltre'    => $statut,
            'categorieFiltre' => $categorieId,
        ]);
    }

    #[Route('/inscription/{id}', name: 'agriculteur_evenement_inscription', methods: ['GET', 'POST'])]
public function inscrire(int $id, ManagerRegistry $doctrine, EvenementRepository $eventRepo, ParticipationRepository $participationRepo): Response
{
    $evenement = $eventRepo->find($id);
    if (!$evenement) {
        throw $this->createNotFoundException('Événement introuvable.');
    }

    $user = $this->getUser();
    if (!$user instanceof User) {
        return $this->redirectToRoute('app_login');
    }

    $existing = $participationRepo->findOneBy([
        'evenement'   => $evenement,
        'utilisateur' => $user,
    ]);
    if ($existing) {
        $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
        return $this->redirectToRoute('agriculteur_evenement_index');
    }

    $participation = new Participation();
    $participation->setEvenement($evenement);
    $participation->setUtilisateur($user);
    $participation->setDateInscription(new \DateTime());
    $participation->setStatutParticipation('Inscrit');
    $participation->setPresence(false);

    $em = $doctrine->getManager();
    $em->persist($participation);
    $em->flush();

    $this->addFlash('success', 'Votre inscription à "' . $evenement->getTitre() . '" a été enregistrée.');
    return $this->redirectToRoute('agriculteur_participation_index');
}
}