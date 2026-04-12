<?php

namespace App\Controller\Evenements;

use App\Entity\Evenements\Evenement;
use App\Form\Evenements\EvenementType;
use App\Repository\Evenements\EvenementRepository;
use App\Repository\Evenements\categorieevenementRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EvenementController extends AbstractController
{
    // ================= LISTE AVEC FILTRES =================
    #[Route('/evenements', name: 'evenement_index')]
    public function index(
        Request $request,
        EvenementRepository $repo,
        categorieevenementRepository $categorieRepo
    ): Response {
        // Récupération des filtres depuis la requête GET
        $search = $request->query->get('search', '');
        $dateDebut = $request->query->get('dateDebut', '');
        $dateFin = $request->query->get('dateFin', '');
        $lieu = $request->query->get('lieu', '');
        $statut = $request->query->get('statut', '');
        $categorieId = $request->query->get('categorie', '');

        // Liste des statuts pour le filtre déroulant
        $statuts = ['Planifié', 'Annulé', 'Terminé'];

        // Liste des catégories pour le filtre déroulant
        $categories = $categorieRepo->findAll();

        // Application des filtres
        $evenements = $repo->findByFilters(
            $search,
            $dateDebut,
            $dateFin,
            $lieu,
            $statut,
            $categorieId ? (int)$categorieId : null
        );

        return $this->render('Evenements/indexEvenement.html.twig', [
            'evenements' => $evenements,
            'total' => count($evenements),
            'search' => $search,
            'dateDebutFiltre' => $dateDebut,
            'dateFinFiltre' => $dateFin,
            'lieuFiltre' => $lieu,
            'statutFiltre' => $statut,
            'categorieFiltre' => $categorieId,
            'statuts' => $statuts,
            'categories' => $categories,
        ]);
    }

    // ================= AJOUTER =================
    #[Route('/evenements/ajouter', name: 'evenement_ajouter')]
    public function ajouter(Request $request, ManagerRegistry $doctrine): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($evenement);
            $em->flush();

            $this->addFlash('success', 'L\'événement "' . $evenement->getTitre() . '" a été ajouté avec succès !');

            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('Evenements/ajouterEvenement.html.twig', [
            'form' => $form->createView(),
            'editMode' => false,
        ]);
    }

    // ================= MODIFIER =================
    #[Route('/evenements/modifier/{id}', name: 'evenement_modifier')]
    public function modifier(int $id, Request $request, ManagerRegistry $doctrine, EvenementRepository $repo): Response
    {
        $evenement = $repo->find($id);

        if (!$evenement) {
            $this->addFlash('error', 'Événement introuvable !');
            return $this->redirectToRoute('evenement_index');
        }

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();
            $this->addFlash('success', 'L\'événement "' . $evenement->getTitre() . '" a été modifié avec succès !');
            return $this->redirectToRoute('evenement_index');
        }

        return $this->render('Evenements/modifierEvenement.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
            'editMode' => true,
        ]);
    }

    // ================= SUPPRIMER =================
    #[Route('/evenements/supprimer/{id}', name: 'evenement_supprimer')]
    public function supprimer(int $id, ManagerRegistry $doctrine, EvenementRepository $repo): Response
    {
        $evenement = $repo->find($id);

        if (!$evenement) {
            $this->addFlash('error', 'Événement introuvable !');
            return $this->redirectToRoute('evenement_index');
        }

        $em = $doctrine->getManager();
        $em->remove($evenement);
        $em->flush();

        $this->addFlash('success', 'L\'événement "' . $evenement->getTitre() . '" a été supprimé avec succès !');

        return $this->redirectToRoute('evenement_index');
    }
}