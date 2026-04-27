<?php

namespace App\Service\Animals;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Psr\Cache\CacheItemInterface;

class OpenFoodFactsService
{
    private const API_BASE_URL = 'https://world.openfoodfacts.org/cgi/search.pl';
    private const CACHE_TTL = 86400; // 24 heures en secondes
    
    /**
     * Mapping des espèces animales vers les termes de recherche alimentaires
     */
    private const SPECIES_SEARCH_TERMS = [
        'Vache' => 'aliments bovins cattle feed',
        'Chèvre' => 'aliments chèvres goat feed',
        'Mouton' => 'aliments moutons sheep feed',
        'Cheval' => 'aliments chevaux horse feed',
        'Chien' => 'croquettes chien dog food kibble',
        'Chat' => 'croquettes chat cat food kibble',
    ];

    /**
     * Données fictives pour le développement/test (fallback)
     */
    private const FALLBACK_PRODUCTS = [
        'Vache' => [
            ['name' => 'Aliment Bovin Premium Maïs & Luzerne', 'brand' => 'FarmGold', 'image' => 'https://images.openfoodfacts.org/images/products/372/100/021/3721/front_fr.3.400.jpg'],
            ['name' => 'Concentré Laitier HighProtein 20%', 'brand' => 'AgriCare', 'image' => 'https://images.openfoodfacts.org/images/products/860/015/030/1000/front_fr.4.400.jpg'],
            ['name' => 'Minéraux & Vitamines Bovins', 'brand' => 'NutriVet', 'image' => 'https://images.openfoodfacts.org/images/products/625/002/700/2000/front_fr.100.400.jpg'],
        ],
        'Chien' => [
            ['name' => 'Croquettes Premium Poulet & Riz', 'brand' => 'DogPro', 'image' => 'https://images.openfoodfacts.org/images/products/500/150/201/0000/front_fr.5.400.jpg'],
            ['name' => 'Aliment Complet Boeuf Légumes', 'brand' => 'PetNutrition', 'image' => 'https://images.openfoodfacts.org/images/products/200/500/001/9000/front_fr.50.400.jpg'],
            ['name' => 'Granulés Poisson & Patate Douce', 'brand' => 'HealthyDog', 'image' => 'https://images.openfoodfacts.org/images/products/300/425/101/1000/front_fr.15.400.jpg'],
        ],
        'Chat' => [
            ['name' => 'Croquettes Poulet Premium', 'brand' => 'CatGourmet', 'image' => 'https://images.openfoodfacts.org/images/products/750/200/501/0000/front_fr.8.400.jpg'],
            ['name' => 'Pâtée Poisson de Mer', 'brand' => 'FelixFood', 'image' => 'https://images.openfoodfacts.org/images/products/650/300/001/5000/front_fr.22.400.jpg'],
            ['name' => 'Granulés Multiprotéines Équilibrés', 'brand' => 'VetChoice', 'image' => 'https://images.openfoodfacts.org/images/products/500/100/505/9000/front_fr.33.400.jpg'],
        ],
        'Chèvre' => [
            ['name' => 'Granulés Luzerne Premium', 'brand' => 'CapraNutrition', 'image' => 'https://images.openfoodfacts.org/images/products/400/550/001/2000/front_fr.12.400.jpg'],
            ['name' => 'Aliment Lactation Chèvres', 'brand' => 'FarmCare', 'image' => 'https://images.openfoodfacts.org/images/products/350/225/301/7000/front_fr.18.400.jpg'],
            ['name' => 'Minéraux Spécial Reproduction', 'brand' => 'VeterPro', 'image' => 'https://images.openfoodfacts.org/images/products/600/425/001/3000/front_fr.25.400.jpg'],
        ],
        'Mouton' => [
            ['name' => 'Granulés Luzerne & Orge', 'brand' => 'SheepCare', 'image' => 'https://images.openfoodfacts.org/images/products/500/650/101/1000/front_fr.11.400.jpg'],
            ['name' => 'Aliment Lait Moutons Laitiers', 'brand' => 'DairySheep', 'image' => 'https://images.openfoodfacts.org/images/products/450/325/501/6000/front_fr.19.400.jpg'],
            ['name' => 'Supplément Vitaminé Moutons', 'brand' => 'ElevagePro', 'image' => 'https://images.openfoodfacts.org/images/products/550/525/201/4000/front_fr.29.400.jpg'],
        ],
        'Cheval' => [
            ['name' => 'Granulés Avoine & Luzerne', 'brand' => 'EquinePro', 'image' => 'https://images.openfoodfacts.org/images/products/600/750/101/8000/front_fr.14.400.jpg'],
            ['name' => 'Complément Énergétique Chevaux Sport', 'brand' => 'PerformanceSteed', 'image' => 'https://images.openfoodfacts.org/images/products/700/425/301/5000/front_fr.21.400.jpg'],
            ['name' => 'Minéraux Articulations & Tendons', 'brand' => 'JointCare', 'image' => 'https://images.openfoodfacts.org/images/products/650/625/001/9000/front_fr.27.400.jpg'],
        ],
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache
    ) {
    }

    /**
     * Récupère les suggestions alimentaires pour une espèce donnée
     *
     * @return array<int, array{name: string, brand: string, image: string|null}>
     */
    public function getFoodSuggestionsForSpecies(string $espece, int $pageSize = 3): array
    {
        // Pour Chien, Chat et Cheval: retourner les suggestions prédéfinies directement
        $predefinedSpecies = ['Chien', 'Chat', 'Cheval'];
        if (in_array($espece, $predefinedSpecies, true)) {
            return $this->getFallbackProducts($espece, $pageSize);
        }

        $searchTerms = self::SPECIES_SEARCH_TERMS[$espece] ?? null;
        
        if ($searchTerms === null) {
            return []; // Espèce non reconnue
        }

        // Créer une clé de cache unique pour cette espèce
        $cacheKey = 'openfoodfacts_' . strtolower(str_replace(' ', '_', $espece));

        try {
            // Utiliser le cache pour éviter les appels répétés à l'API
            $products = $this->cache->get($cacheKey, function (CacheItemInterface $item) use ($searchTerms, $pageSize, $espece) {
                $item->expiresAfter(self::CACHE_TTL);
                return $this->fetchProductsFromApi($searchTerms, $pageSize, $espece);
            });

            return $products;
        } catch (\Exception $e) {
            error_log('OpenFoodFacts Service Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array{name: string, brand: string, image: string|null}>
     */
    private function fetchProductsFromApi(string $searchTerms, int $pageSize, string $espece = ''): array
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE_URL, [
                'query' => [
                    'search_terms' => $searchTerms,
                    'json' => '1',
                    'page_size' => 10,
                ],
                'timeout' => 10,
            ]);

            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                return $this->getFallbackProducts($espece, $pageSize);
            }

            $data = $response->toArray();
            $products = [];

            if (isset($data['products']) && is_array($data['products'])) {
                foreach ($data['products'] as $product) {
                    $filteredProduct = $this->filterProductData($product);
                    if ($filteredProduct) {
                        $products[] = $filteredProduct;
                        if (count($products) >= $pageSize) {
                            break;
                        }
                    }
                }
            }

            if (count($products) === 0) {
                return $this->getFallbackProducts($espece, $pageSize);
            }

            return $products;
        } catch (\Exception $e) {
            return $this->getFallbackProducts($espece, $pageSize);
        }
    }

    /**
     * @return array<int, array{name: string, brand: string, image: string|null}>
     */
    private function getFallbackProducts(string $espece, int $pageSize): array
    {
        $fallback = self::FALLBACK_PRODUCTS[$espece] ?? self::FALLBACK_PRODUCTS['Chien'];
        return array_slice($fallback, 0, $pageSize);
    }

    /**
     * @param array<string, mixed> $product
     * @return array{name: string, brand: string, image: string|null}|null
     */
    private function filterProductData(array $product): ?array
    {
        if (!isset($product['product_name'])) {
            return null;
        }

        $filteredProduct = [
            'name' => (string) $product['product_name'],
            'brand' => $product['brands'] ?? 'Marque non spécifiée',
            'image' => null,
        ];

        if (isset($product['image_front_url']) && !empty($product['image_front_url'])) {
            $filteredProduct['image'] = $product['image_front_url'];
        } elseif (isset($product['image_url']) && !empty($product['image_url'])) {
            $filteredProduct['image'] = $product['image_url'];
        }

        return $filteredProduct;
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE_URL, [
                'query' => [
                    'search_terms' => 'test',
                    'json' => '1',
                    'page_size' => 1,
                ],
                'timeout' => 5,
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
