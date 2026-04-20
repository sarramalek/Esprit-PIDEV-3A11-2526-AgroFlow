<?php

namespace App\Service\Animals;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Psr\Cache\CacheItemInterface;

class WikipediaService
{
    private const API_BASE_URL = 'https://fr.wikipedia.org/api/rest_v1/page/summary/';
    private const CACHE_TTL = 604800; // 7 jours

    private HttpClientInterface $httpClient;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    /**
     * Recherche une page Wikipedia et retourne le résumé
     */
    public function searchPage(string $query): ?array
    {
        if (empty(trim($query))) {
            return null;
        }

        $cacheKey = 'wikipedia_' . md5(strtolower($query));
        
        return $this->cache->get($cacheKey, function (CacheItemInterface $item) use ($query) {
            $item->expiresAfter(self::CACHE_TTL);
            
            try {
                $response = $this->httpClient->request('GET', self::API_BASE_URL . urlencode($query), [
                    'timeout' => 5,
                ]);

                if ($response->getStatusCode() !== 200) {
                    return null;
                }

                $data = $response->toArray();
                
                return [
                    'title' => $data['title'] ?? $query,
                    'extract' => $data['extract'] ?? 'Aucun résumé disponible',
                    'image' => $data['thumbnail']['source'] ?? null,
                    'url' => $data['content_urls']['mobile']['page'] ?? 'https://fr.wikipedia.org',
                ];
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    /**
     * Suggestions rapides médicales basées sur des termes courants
     */
    public function getQuickSuggestions(string $diagnostic = '', string $traitement = ''): array
    {
        $suggestions = [];
        
        // Mots clés courants en médecine vétérinaire
        $medicalKeywords = [
            'infection' => 'Infection',
            'antibiotique' => 'Antibiotique',
            'vaccin' => 'Vaccin',
            'allergie' => 'Allergie',
            'blessure' => 'Blessure',
            'fracture' => 'Fracture',
            'arthrite' => 'Arthrite',
            'diarrhée' => 'Diarrhée',
            'vomissement' => 'Vomissement',
            'pneumonie' => 'Pneumonie',
            'gastrite' => 'Gastrite',
            'parasites' => 'Parasites',
            'dermatite' => 'Dermatite',
            'otite' => 'Otite',
            'cataracte' => 'Cataracte',
            'tumeur' => 'Tumeur',
            'anesthésie' => 'Anesthésie',
            'chirurgie' => 'Chirurgie',
            'radiographie' => 'Radiographie',
            'échographie' => 'Échographie',
        ];

        // Extraire les mots clés du diagnostic
        $textToAnalyze = strtolower($diagnostic . ' ' . $traitement);
        
        foreach ($medicalKeywords as $keyword => $display) {
            if (strpos($textToAnalyze, $keyword) !== false) {
                $suggestions[] = [
                    'label' => $display,
                    'keyword' => $keyword,
                ];
            }
        }

        // Ajouter d'autres suggestions par défaut si aucune trouvée
        if (empty($suggestions)) {
            $suggestions = [
                ['label' => 'Santé animale', 'keyword' => 'santé animale'],
                ['label' => 'Médecine vétérinaire', 'keyword' => 'médecine vétérinaire'],
                ['label' => 'Antibiotique', 'keyword' => 'antibiotique'],
                ['label' => 'Vaccination', 'keyword' => 'vaccination'],
            ];
        }

        return array_slice($suggestions, 0, 4); // Limiter à 4 suggestions
    }
}
