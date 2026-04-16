<?php

namespace App\Controller\Evenements;

use App\Entity\Evenements\Participation;
use App\Form\Evenements\ParticipationType;
use App\Repository\Evenements\ParticipationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ParticipationController extends AbstractController
{
    #[Route('/participations', name: 'participation_index')]
    public function index(Request $request, ParticipationRepository $repo): Response
    {
        $search          = $request->query->get('search', '');
        $dateInscription = $request->query->get('dateInscription', '');
        $statut          = $request->query->get('statut', '');
        $presence        = $request->query->get('presence', '');
        $userId          = $request->query->get('userId', '');

        $participations = $repo->findByFilters($search, $dateInscription, $statut, $presence, $userId);

        $statuts   = ['Inscrit', 'Confirmé', 'Annulé'];
        $presences = ['Oui', 'Non'];

        return $this->render('Evenements/indexParticipation.html.twig', [
            'participations' => $participations,
            'total'          => count($participations),
            'search'         => $search,
            'dateInscriptionFiltre' => $dateInscription,
            'statutFiltre'   => $statut,
            'presenceFiltre' => $presence,
            'userIdFiltre'   => $userId,
            'statuts'        => $statuts,
            'presences'      => $presences,
        ]);
    }

    #[Route('/participations/ajouter', name: 'participation_ajouter')]
    public function ajouter(Request $request, ManagerRegistry $doctrine): Response
    {
        $participation = new Participation();
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($participation);
            $em->flush();

            $this->addFlash('success', 'La participation a été ajoutée avec succès !');
            return $this->redirectToRoute('participation_index');
        }

        return $this->render('Evenements/ajouterParticipation.html.twig', [
            'form' => $form->createView(),
            'editMode' => false,
        ]);
    }

    #[Route('/participations/modifier/{id}', name: 'participation_modifier')]
    public function modifier(int $id, Request $request, ManagerRegistry $doctrine, ParticipationRepository $repo): Response
    {
        $participation = $repo->find($id);

        if (!$participation) {
            $this->addFlash('error', 'Participation introuvable !');
            return $this->redirectToRoute('participation_index');
        }

        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'La participation a été modifiée avec succès !');
            return $this->redirectToRoute('participation_index');
        }

        return $this->render('Evenements/modifierParticipation.html.twig', [
            'form' => $form->createView(),
            'participation' => $participation,
            'editMode' => true,
        ]);
    }

    #[Route('/participations/supprimer/{id}', name: 'participation_supprimer')]
    public function supprimer(int $id, ManagerRegistry $doctrine, ParticipationRepository $repo): Response
    {
        $participation = $repo->find($id);

        if (!$participation) {
            $this->addFlash('error', 'Participation introuvable !');
            return $this->redirectToRoute('participation_index');
        }

        $em = $doctrine->getManager();
        $em->remove($participation);
        $em->flush();

        $this->addFlash('success', 'La participation a été supprimée avec succès !');
        return $this->redirectToRoute('participation_index');
    }
}