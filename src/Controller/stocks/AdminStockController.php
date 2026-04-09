<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\User\User;
use App\Form\stocks\ArticleAdminType;
use App\Repository\stocks\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/gestion-stocks')]
class AdminStockController extends AbstractController
{
    #[Route('/', name: 'admin_stock_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        $articles = $articleRepository->findByAdminCin((int) $currentUser->getCin());

        return $this->render('stocks/article/admin_index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/ajouter-produit', name: 'admin_stock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $article = new Article();
        // On ne fait PAS de setUser ici, on laisse le formulaire s'en charger

        $form = $this->createForm(ArticleAdminType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User|null $currentUser */
            $currentUser = $this->getUser();
            if ($currentUser && \in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
                $article->setIdAdmin($currentUser->getCin());
            }

            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Votre article a été ajouté avec succès.');
            return $this->redirectToRoute('admin_stock_index');
        }

        return $this->render('stocks/article/Admin_new_article.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }
        if ((int)($article->getIdAdmin() ?? 0) !== (int)$currentUser->getCin()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres articles.');
        }

        $form = $this->createForm(ArticleAdminType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'L\'article a été modifié avec succès.');
            return $this->redirectToRoute('admin_stock_index');
        }

        return $this->render('stocks/article/admin_edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }
        if ((int)($article->getIdAdmin() ?? 0) !== (int)$currentUser->getCin()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres articles.');
        }

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();

            $this->addFlash('success', 'L\'article a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_stock_index');
    }
}
