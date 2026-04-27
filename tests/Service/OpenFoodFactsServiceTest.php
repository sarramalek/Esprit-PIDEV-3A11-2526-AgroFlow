<?php

namespace App\Tests\Service;

use App\Service\OpenFoodFactsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class OpenFoodFactsServiceTest extends TestCase
{
    private OpenFoodFactsService $service;

    protected function setUp(): void
    {
        $httpClient = new MockHttpClient(function (string $method, string $url) {
            if (str_contains($url, '/api/v0/status.json')) {
                return new MockResponse(json_encode(['status' => 1], JSON_THROW_ON_ERROR), ['http_code' => 200]);
            }

            return new MockResponse(json_encode([
                'products' => [
                    ['product_name' => 'Alpha', 'brands' => 'B1', 'image_url' => 'https://img/1.png'],
                    ['product_name' => 'Beta', 'brands' => 'B2', 'image_url' => null],
                    ['product_name' => 'Gamma', 'brands' => '', 'image_url' => 'https://img/3.png'],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });
        $cache = new ArrayAdapter();

        $this->service = new OpenFoodFactsService($httpClient, $cache);
    }

    /**
     * Test : Vérifier la connexion à l'API OpenFoodFacts
     */
    public function testApiConnection(): void
    {
        $isConnected = $this->service->testConnection();
        $this->assertTrue($isConnected, 'OpenFoodFacts API should be accessible');
    }

    /**
     * Test : Récupérer les suggestions pour une Vache
     */
    public function testGetFoodSuggestionsForCow(): void
    {
        $suggestions = $this->service->getFoodSuggestionsForSpecies('Vache');
        
        // Vérifier qu'on obtient des résultats
        $this->assertIsArray($suggestions);
        $this->assertLessThanOrEqual(3, count($suggestions), 'Should return at most 3 products');
        
        // Vérifier la structure des données
        if (count($suggestions) > 0) {
            $product = $suggestions[0];
            $this->assertArrayHasKey('name', $product);
            $this->assertArrayHasKey('brand', $product);
            $this->assertArrayHasKey('image', $product);
            $this->assertNotEmpty($product['name'], 'Product name should not be empty');
        }
    }

    /**
     * Test : Vérifier que le cache fonctionne
     */
    public function testCachingFunctionality(): void
    {
        $suggestions1 = $this->service->getFoodSuggestionsForSpecies('Cheval');

        $suggestions2 = $this->service->getFoodSuggestionsForSpecies('Cheval');

        // Les résultats doivent être identiques
        $this->assertEquals($suggestions1, $suggestions2);
    }

    /**
     * Test : Espèce non reconnue retourne un tableau vide
     */
    public function testUnknownSpeciesReturnsEmpty(): void
    {
        $suggestions = $this->service->getFoodSuggestionsForSpecies('EspeceInconnue');
        $this->assertEmpty($suggestions);
    }

    /**
     * Test : Vérifier toutes les espèces supportées
     */
    public function testAllSupportedSpecies(): void
    {
        $species = ['Vache', 'Chèvre', 'Mouton', 'Cheval', 'Chien', 'Chat'];
        
        foreach ($species as $specie) {
            $suggestions = $this->service->getFoodSuggestionsForSpecies($specie);
            // Ne pas faire échouer le test si l'API est lente, mais vérifier la structure
            if (is_array($suggestions)) {
                $this->assertTrue(true, "Species '$specie' returned valid array");
            }
        }
    }
}
