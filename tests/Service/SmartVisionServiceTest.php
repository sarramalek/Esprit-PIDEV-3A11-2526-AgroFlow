<?php

namespace App\Tests\Service;

use App\Service\SmartVisionService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SmartVisionServiceTest extends TestCase
{
    public function testAnalyzeImageRetourneNomEtCategorie(): void
    {
        // Mock du HttpClient
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        
        $responseArray = [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => '{"nom": "YaraMila", "categorie": "Engrais"}'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $responseMock->method('toArray')->willReturn($responseArray);
        $httpClientMock->method('request')->willReturn($responseMock);

        $smartVisionService = new SmartVisionService($httpClientMock, 'fake_api_key');
        
        $resultat = $smartVisionService->analyzeImage('fake_base64_string');
        
        $this->assertIsArray($resultat);
        $this->assertArrayHasKey('nom', $resultat);
        $this->assertArrayHasKey('categorie', $resultat);
        $this->assertEquals('YaraMila', $resultat['nom']);
        $this->assertEquals('Engrais', $resultat['categorie']);
    }

    public function testAnalyzeImageLanceExceptionSiCleManquante(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $smartVisionService = new SmartVisionService($httpClientMock, '');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("La clé d'API GEMINI_API_KEY est manquante dans le fichier .env");

        $smartVisionService->analyzeImage('fake_base64_string');
    }
}
