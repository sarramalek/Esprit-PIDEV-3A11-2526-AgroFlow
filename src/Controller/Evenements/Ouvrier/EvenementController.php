<?php

namespace App\Controller\Evenements\Ouvrier;

use App\Entity\Evenements\Participation;
use App\Repository\Evenements\EvenementRepository;
use App\Repository\Evenements\ParticipationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ouvrier/evenements')]
#[IsGranted('ROLE_OUVRIER')]
class EvenementController extends AbstractController
{
    #[Route('', name: 'ouvrier_evenement_index')]
    public function index(Request $request, EvenementRepository $repo): Response
    {
        $search      = $request->query->get('search', '');
        $dateDebut   = $request->query->get('dateDebut', '');
        $dateFin     = $request->query->get('dateFin', '');
        $lieu        = $request->query->get('lieu', '');
        $statut      = $request->query->get('statut', '');
        $categorieId = $request->query->get('categorie', '');

        $evenements = $repo->findByFilters($search, $dateDebut, $dateFin, $lieu, $statut, $categorieId ? (int)$categorieId : null);

        return $this->render('Evenements/indexEvenementOuvrier.html.twig', [
            'evenements'      => $evenements,
            'total'           => count($evenements),
            'search'          => $search,
            'dateDebutFiltre' => $dateDebut,
            'dateFinFiltre'   => $dateFin,
            'lieuFiltre'      => $lieu,
            'statutFiltre'    => $statut,
            'categorieFiltre' => $categorieId,
        ]);
    }

    #[Route('/inscription/{id}', name: 'ouvrier_evenement_inscription')]
    public function inscrire(int $id, EvenementRepository $eventRepo, ParticipationRepository $participationRepo, ManagerRegistry $doctrine): Response
    {
        $evenement = $eventRepo->find($id);
        if (!$evenement) {
            throw $this->createNotFoundException('Événement introuvable.');
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $existing = $participationRepo->findOneBy([
            'evenement'   => $evenement,
            'utilisateur' => $user,
        ]);
        if ($existing) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
            return $this->redirectToRoute('ouvrier_evenement_index');
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
        return $this->redirectToRoute('ouvrier_participation_index');
    }
}