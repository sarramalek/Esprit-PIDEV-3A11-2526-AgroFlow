<?php

namespace App\Tests\Service;

use App\Service\Animals\OpenFoodFactsService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OpenFoodFactsServiceTest extends KernelTestCase
{
    private OpenFoodFactsService $service;

    protected function setUp(): void
    {
        self::bootKernel();

        // Mock HTTP client pour ne pas appeler l'API réelle
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('toArray')->willReturn([
            'products' => [
                ['product_name' => 'TestProduit', 'brands' => 'TestBrand', 'image_url' => 'http://test.com/img.jpg']
            ]
        ]);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('request')->willReturn($mockResponse);

        $cache = static::getContainer()->get('cache.app');

        $this->service = new OpenFoodFactsService($mockHttpClient, $cache);
    }

    public function testApiConnection(): void
    {
        // On teste juste que le service s'instancie correctement
        $this->assertInstanceOf(OpenFoodFactsService::class, $this->service);
    }

    public function testGetFoodSuggestionsForCow(): void
    {
        $suggestions = $this->service->getFoodSuggestionsForSpecies('Vache');
        $this->assertLessThanOrEqual(3, count($suggestions));
    }

    public function testCachingFunctionality(): void
    {
        $suggestions1 = $this->service->getFoodSuggestionsForSpecies('Cheval');
        $suggestions2 = $this->service->getFoodSuggestionsForSpecies('Cheval');
        $this->assertEquals($suggestions1, $suggestions2);
    }

    public function testUnknownSpeciesReturnsEmpty(): void
    {
        $suggestions = $this->service->getFoodSuggestionsForSpecies('EspeceInconnue');
        $this->assertEmpty($suggestions);
    }

    public function testAllSupportedSpecies(): void
    {
        $species = ['Vache', 'Chèvre', 'Mouton', 'Cheval', 'Chien', 'Chat'];
        foreach ($species as $specie) {
            $suggestions = $this->service->getFoodSuggestionsForSpecies($specie);
            $this->assertLessThanOrEqual(3, count($suggestions));
        }
    }
}