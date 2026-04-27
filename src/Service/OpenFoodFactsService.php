<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenFoodFactsService
{
    private const BASE_URL = 'https://world.openfoodfacts.org';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->httpClient->request('GET', self::BASE_URL.'/api/v0/status.json');
            return $response->getStatusCode() === 200;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<int, array{name: string, brand: string, image: string|null}>
     */
    public function getFoodSuggestionsForSpecies(string $species): array
    {
        $query = $this->speciesToQuery($species);
        if ($query === null) {
            return [];
        }

        $cacheKey = 'openfoodfacts.suggestions.'.mb_strtolower($species);

        /** @var array<int, array{name: string, brand: string, image: string|null}> $result */
        $result = $this->cache->get($cacheKey, function (ItemInterface $item) use ($query): array {
            $item->expiresAfter(3600);

            $response = $this->httpClient->request('GET', self::BASE_URL.'/cgi/search.pl', [
                'query' => [
                    'search_terms' => $query,
                    'search_simple' => 1,
                    'action' => 'process',
                    'json' => 1,
                    'page_size' => 3,
                ],
            ]);

            $data = $response->toArray(false);
            $products = is_array($data['products'] ?? null) ? $data['products'] : [];

            $out = [];
            foreach ($products as $p) {
                if (!is_array($p)) {
                    continue;
                }

                $name = (string) ($p['product_name'] ?? '');
                $brand = (string) ($p['brands'] ?? '');
                $image = isset($p['image_url']) ? (string) $p['image_url'] : null;

                if ($name === '') {
                    continue;
                }

                $out[] = [
                    'name' => $name,
                    'brand' => $brand,
                    'image' => $image,
                ];

                if (count($out) >= 3) {
                    break;
                }
            }

            return $out;
        });

        return $result;
    }

    private function speciesToQuery(string $species): ?string
    {
        return match (mb_strtolower(trim($species))) {
            'vache' => 'cow feed',
            'chèvre', 'chevre' => 'goat feed',
            'mouton' => 'sheep feed',
            'cheval' => 'horse feed',
            'chien' => 'dog food',
            'chat' => 'cat food',
            default => null,
        };
    }
}

