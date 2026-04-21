<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Categorie;
use App\Entity\User\User;
use App\Form\stocks\CategorieAdminType;
use App\Repository\stocks\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/gestion-stocks')]
class Admin_CategorieController extends AbstractController
{
    #[Route('/categories', name: 'admin_categories_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        $categories = $categorieRepository->findByAdminCin((int) $currentUser->getCin());

        return $this->render('stocks/categorie/admin_index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/ajouter-categorie', name: 'admin_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieAdminType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User|null $currentUser */
            $currentUser = $this->getUser();
            if ($currentUser && \in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
                $categorie->setAgriculteur($currentUser->getCin());
            }

            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Votre catégorie a été ajoutée avec succès.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('stocks/categorie/Admin_new_categorie.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categorie/{id}/edit', name: 'admin_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $em): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }
        if ((int)($categorie->getAgriculteur() ?? 0) !== (int)$currentUser->getCin()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres catégories.');
        }

        $form = $this->createForm(CategorieAdminType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'La catégorie a été modifiée avec succès.');
            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('stocks/categorie/admin_edit_categorie.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categorie/{id}/delete', name: 'admin_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $em): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }
        if ((int)($categorie->getAgriculteur() ?? 0) !== (int)$currentUser->getCin()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres catégories.');
        }

        if ($this->isCsrfTokenValid('delete_categorie' . $categorie->getId(), $request->request->get('_token'))) {
            $em->remove($categorie);
            $em->flush();
            $this->addFlash('success', 'La catégorie a été supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_categories_index');
    }
}
