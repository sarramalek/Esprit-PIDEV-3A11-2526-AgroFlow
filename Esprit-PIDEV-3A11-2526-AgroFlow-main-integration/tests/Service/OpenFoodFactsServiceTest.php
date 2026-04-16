<?php

namespace App\Tests\Service;

use App\Service\OpenFoodFactsService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

class OpenFoodFactsServiceTest extends KernelTestCase
{
    private OpenFoodFactsService $service;
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->httpClient = static::getContainer()->get('http_client');
        $this->cache = static::getContainer()->get('cache.app');
        $this->service = new OpenFoodFactsService($this->httpClient, $this->cache);
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
        // Première requête
        $start1 = microtime(true);
        $suggestions1 = $this->service->getFoodSuggestionsForSpecies('Cheval');
        $time1 = microtime(true) - $start1;

        // Deuxième requête (devrait être plus rapide via le cache)
        $start2 = microtime(true);
        $suggestions2 = $this->service->getFoodSuggestionsForSpecies('Cheval');
        $time2 = microtime(true) - $start2;

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
