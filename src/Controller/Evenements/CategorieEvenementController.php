<?php

namespace App\Controller\Evenements;

use App\Entity\Evenements\categorieevenement;
use App\Form\Evenements\categorieevenementType;
use App\Repository\Evenements\categorieevenementRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategorieEvenementController extends AbstractController
{
    // ================= LISTE =================
    #[Route('/CategoriesEvenement', name: 'categorie_evenement_index')]
public function index(Request $request, categorieevenementRepository $repo): Response
{
    $recherche = $request->query->get('recherche', '');

    if ($recherche) {
        $categories = $repo->createQueryBuilder('c')
            ->where('c.nomCategorie LIKE :q')
            ->setParameter('q', '%' . $recherche . '%')
            ->getQuery()
            ->getResult();
    } else {
        $categories = $repo->findAll();
    }

    return $this->render('Evenements/indexCategorie.html.twig', [
        'categories' => $categories,        // ← était 'list'
        'total'      => count($categories),
        'recherche'  => $recherche,         // ← manquait complètement
    ]);
}

    // ================= AJOUTER =================
    #[Route('/ajouterCategorieEvenement', name: 'categorie_evenement_ajouter')]
    public function ajouter(Request $request, ManagerRegistry $d): Response
    {
        $categorie = new categorieevenement();
        $form = $this->createForm(categorieevenementType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $d->getManager();
            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'La catégorie "' . $categorie->getNomCategorie() . '" a été ajoutée avec succès !');

            return $this->redirectToRoute('categorie_evenement_index');
        }

        return $this->render('Evenements/ajouterCategorie.html.twig', [
            'form'     => $form->createView(),
            'editMode' => false,
        ]);
    }

    // ================= MODIFIER =================
    #[Route('/modifierCategorieEvenement/{id}', name: 'categorie_evenement_modifier')]
    public function modifier(int $id, Request $request, ManagerRegistry $d, categorieevenementRepository $repo): Response
    {
        $categorie = $repo->find($id);

        if (!$categorie) {
            $this->addFlash('error', 'Catégorie introuvable !');
            return $this->redirectToRoute('categorie_evenement_index');
        }

        $form = $this->createForm(categorieevenementType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $d->getManager();
            $em->flush();

            $this->addFlash('success', 'La catégorie "' . $categorie->getNomCategorie() . '" a été modifiée avec succès !');

            return $this->redirectToRoute('categorie_evenement_index');
        }

        // Réutilise ajouterCategorie.html.twig avec editMode = true
        return $this->render('Evenements/ajouterCategorie.html.twig', [
            'form'      => $form->createView(),
            'editMode'  => true,
            'categorie' => $categorie,
        ]);
    }

    // ================= SUPPRIMER =================
    #[Route('/supprimerCategorieEvenement/{id}', name: 'categorie_evenement_supprimer')]
    public function supprimer(int $id, ManagerRegistry $d, categorieevenementRepository $repo): Response
    {
        $categorie = $repo->find($id);

        if (!$categorie) {
            $this->addFlash('error', 'Catégorie introuvable !');
            return $this->redirectToRoute('categorie_evenement_index');
        }

        $em = $d->getManager();
        $em->remove($categorie);
        $em->flush();

        $this->addFlash('success', 'La catégorie "' . $categorie->getNomCategorie() . '" a été supprimée avec succès !');

        return $this->redirectToRoute('categorie_evenement_index');
    }
}