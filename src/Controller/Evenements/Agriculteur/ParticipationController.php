<?php

namespace App\Controller\Evenements\Agriculteur;

use App\Entity\Evenements\Participation;
use App\Form\ModifierParticipationUserType;
use App\Repository\Evenements\ParticipationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agriculteur/participations')]
#[IsGranted('ROLE_AGRICULTEUR')]
class ParticipationController extends AbstractController
{
    #[Route('/', name: 'agriculteur_participation_index')]
    public function index(Request $request, ParticipationRepository $repo): Response
    {
        $user = $this->getUser();
        $search          = $request->query->get('search', '');
        $dateInscription = $request->query->get('dateInscription', '');
        $statut          = $request->query->get('statut', '');
        $presence        = $request->query->get('presence', '');

        $participations = $repo->findByFilters(
            $search,
            $dateInscription,
            $statut,
            $presence,
            (string) $user->getCin()
        );

        $total    = count($participations);
        $confirme = count(array_filter($participations, fn($p) => in_array(strtolower($p->getStatutParticipation()), ['confirmé', 'confirme'])));
        $inscrit  = count(array_filter($participations, fn($p) => strtolower($p->getStatutParticipation()) === 'inscrit'));
        $annule   = count(array_filter($participations, fn($p) => in_array(strtolower($p->getStatutParticipation()), ['annulé', 'annule'])));

        return $this->render('Evenements/indexParticipationUser.html.twig', [
            'participations'   => $participations,
            'total'            => $total,
            'confirme'         => $confirme,
            'inscrit'          => $inscrit,
            'annule'           => $annule,
            'search'           => $search,
            'dateInscriptionFiltre' => $dateInscription,
            'statutFiltre'     => $statut,
            'presenceFiltre'   => $presence,
        ]);
    }

    #[Route('/modifier/{id}', name: 'agriculteur_participation_modifier', methods: ['GET', 'POST'])]
    public function modifier(int $id, Request $request, ManagerRegistry $doctrine, ParticipationRepository $repo): Response
    {
        $participation = $repo->find($id);
        $user = $this->getUser();

        if (!$participation || $participation->getUtilisateur()->getCin() !== $user->getCin()) {
            throw $this->createNotFoundException('Participation introuvable.');
        }

        if (in_array(strtolower($participation->getStatutParticipation()), ['annulé', 'annule'])) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier une participation annulée.');
            return $this->redirectToRoute('agriculteur_participation_index');
        }

        $form = $this->createForm(ModifierParticipationUserType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'Votre participation a été mise à jour.');
            return $this->redirectToRoute('agriculteur_participation_index');
        }

        return $this->render('Evenements//modifierParticipationUser.html.twig', [
            'form' => $form->createView(),
            'participation' => $participation,
        ]);
    }

    #[Route('/annuler/{id}', name: 'agriculteur_participation_annuler', methods: ['POST'])]
    public function annuler(int $id, ManagerRegistry $doctrine, ParticipationRepository $repo): Response
    {
        $participation = $repo->find($id);
        $user = $this->getUser();

        if (!$participation || $participation->getUtilisateur()->getCin() !== $user->getCin()) {
            throw $this->createNotFoundException('Participation introuvable.');
        }

        if (in_array(strtolower($participation->getStatutParticipation()), ['annulé', 'annule'])) {
            $this->addFlash('warning', 'Cette participation est déjà annulée.');
            return $this->redirectToRoute('agriculteur_participation_index');
        }

        $participation->setStatutParticipation('Annulé');
        $doctrine->getManager()->flush();

        $this->addFlash('success', 'Votre participation a été annulée.');
        return $this->redirectToRoute('agriculteur_participation_index');
    }
}