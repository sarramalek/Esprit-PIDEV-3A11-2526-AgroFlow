<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\Categorie;
use App\Service\SmartVisionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/api/vision')]
class VisionController extends AbstractController
{
    #[Route('/analyze', name: 'api_vision_analyze', methods: ['POST'])]
    public function analyze(Request $request, SmartVisionService $visionService, EntityManagerInterface $em): JsonResponse
    {
        $file = $request->files->get('image');
        
        if (!$file) {
            return new JsonResponse(['error' => 'Aucune image fournie.'], 400);
        }

        try {
            $mimeType = $file->getMimeType();
            $base64Image = base64_encode(file_get_contents($file->getPathname()));

            // 1. Appel à l'IA Gemini
            $resultatIA = $visionService->analyzeImage($base64Image, $mimeType);
            
            $nomExtrait = $resultatIA['nom'] ?? null;
            $catExtraite = $resultatIA['categorie'] ?? null;

            if (!$nomExtrait && !$catExtraite) {
                return new JsonResponse(['error' => "L'IA n'a rien pu identifier sur l'image."], 404);
            }

            $user = $this->getUser();
            $matchType = 'none';
            $articleId = null;
            $categoryId = null;

            // 2. Logique de Matching Symfony
            if ($nomExtrait) {
                $articleRepo = $em->getRepository(Article::class);
                
                // Tentative 1 : Match direct (LIKE)
                $qb = $articleRepo->createQueryBuilder('a')
                    ->where('a.user = :user')
                    ->andWhere('a.nom LIKE :nom')
                    ->setParameter('user', $user)
                    ->setParameter('nom', '%' . $nomExtrait . '%')
                    ->setMaxResults(1);
                
                $article = $qb->getQuery()->getOneOrNullResult();

                // Tentative 2 : Si pas de match, on cherche par mots-clés (plus flexible)
                if (!$article) {
                    $mots = explode(' ', $nomExtrait);
                    foreach ($mots as $mot) {
                        if (strlen($mot) < 3) continue; // On ignore les petits mots
                        $qb = $articleRepo->createQueryBuilder('a')
                            ->where('a.user = :user')
                            ->andWhere('a.nom LIKE :mot')
                            ->setParameter('user', $user)
                            ->setParameter('mot', '%' . $mot . '%')
                            ->setMaxResults(1);
                        $article = $qb->getQuery()->getOneOrNullResult();
                        if ($article) break;
                    }
                }

                if ($article) {
                    $matchType = 'exact';
                    $articleId = $article->getId();
                    $nomExtrait = $article->getNom(); // On utilise le nom officiel
                }
            }

            // Si pas d'article exact, on cherche quand même la catégorie pour aider au pré-remplissage
            if ($matchType === 'none' && $catExtraite) {
                $catRepo = $em->getRepository(Categorie::class);
                $qb = $catRepo->createQueryBuilder('c')
                    ->where('c.nom LIKE :cat')
                    ->setParameter('cat', '%' . $catExtraite . '%')
                    ->setMaxResults(1);
                
                $category = $qb->getQuery()->getOneOrNullResult();

                if ($category) {
                    $categoryId = $category->getId();
                }
            }

            // 3. Retour des données structurées pour gérer la redirection côté JS
            return new JsonResponse([
                'success' => true,
                'matchType' => $matchType,
                'articleId' => $articleId,
                'categoryId' => $categoryId,
                'suggestedName' => $nomExtrait,
                'suggestedCategory' => $catExtraite
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => "Erreur lors de l'analyse : " . $e->getMessage()], 500);
        }
    }
}
