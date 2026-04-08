<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Categorie;
// CORRECTION : Le chemin doit être direct vers le dossier Form
use App\Form\stocks\CategorieAdminType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/gestion-stocks')]
class Admin_CategorieController extends AbstractController
{
    #[Route('/ajouter-categorie', name: 'admin_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new Categorie();

        // Maintenant cette classe sera reconnue correctement
        $form = $this->createForm(CategorieAdminType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Catégorie ajoutée !');
            return $this->redirectToRoute('admin_categorie_new');
        }

        return $this->render('stocks/categorie/Admin_new_categorie.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
