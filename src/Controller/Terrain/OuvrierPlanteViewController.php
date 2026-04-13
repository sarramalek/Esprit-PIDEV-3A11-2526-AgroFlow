<?php

namespace App\Controller\Terrain;

use App\Repository\Terrain\PlanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OuvrierPlanteViewController extends AbstractController
{
    #[Route('/ouvrier/plantes', name: 'ouvrier_plantes', methods: ['GET'])]
    public function index(Request $req, PlanteRepository $repo): Response
    {
        $q    = $req->query->get('q', '');
        $sort = $req->query->get('sort', 'nom');

        $plantes = $q ? $repo->search($q) : $repo->findAll();

        usort($plantes, function ($a, $b) use ($sort) {
            return match ($sort) {
                'nom_desc'   => strcmp($b->getNomP(),     $a->getNomP()),
                'eau'        => $a->getBesoinEau()  <=> $b->getBesoinEau(),
                'eau_desc'   => $b->getBesoinEau()  <=> $a->getBesoinEau(),
                'cycle'      => $a->getCycleJours() <=> $b->getCycleJours(),
                'cycle_desc' => $b->getCycleJours() <=> $a->getCycleJours(),
                default      => strcmp($a->getNomP(),     $b->getNomP()),
            };
        });

        return $this->render('ouv/ouvrier_plantes.html.twig', [
            'plantes'       => $plantes,
            'q'             => $q,
            'sort'          => $sort,
            'total'         => count($repo->findAll()),
            'avgBesoinEau'  => $repo->avgBesoinEau(),
            'avgCycleJours' => $repo->avgCycleJours(),
        ]);
    }

    #[Route('/ouvrier/plantes/conseils', name: 'ouvrier_plantes_conseils', methods: ['POST'])]
    public function conseils(Request $req, HttpClientInterface $http): JsonResponse
    {
        $data    = json_decode($req->getContent(), true);
        $nom     = $data['nom']    ?? '';
        $variete = $data['variete'] ?? '';
        $eau     = $data['eau']    ?? '';
        $cycle   = $data['cycle']  ?? '';

        $prompt = "Tu es un agronome expert. Donne des conseils pratiques et détaillés pour cultiver la plante suivante :

Nom : $nom
Variété : $variete
Besoin en eau : $eau L/jour
Cycle de culture : $cycle jours

Structure ta réponse en sections :
1. 🌱 Préparation du sol
2. 💧 Irrigation et arrosage
3. 🌞 Ensoleillement et température
4. 🧪 Fertilisation recommandée
5. 🐛 Maladies et nuisibles fréquents
6. ✂️ Entretien et taille
7. 🍃 Récolte et conservation

Sois précis, pratique et adapté au contexte agricole tunisien. Réponds en français.";

        try {
            $response = $http->request('POST', 'https://api.anthropic.com/v1/messages', [
                'headers' => [
                    'x-api-key'         => $_ENV['ANTHROPIC_API_KEY'],
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ],
                'json' => [
                    'model'      => 'claude-sonnet-4-20250514',
                    'max_tokens' => 1000,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);

            $result = $response->toArray();
            $text   = $result['content'][0]['text'] ?? 'Aucune réponse générée.';

            return new JsonResponse(['success' => true, 'conseil' => $text]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'conseil' => '⚠️ Impossible de générer les conseils : ' . $e->getMessage(),
            ], 500);
        }
    }
}