<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Exception;

class CurrencyService
{
    private HttpClientInterface $httpClient;
    private ?string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    /**
     * Convertit un montant d'une devise étrangère vers le Dinar Tunisien (TND)
     */
    public function convertToTND(float $amount, string $fromCurrency): float
    {
        // Nettoyage au cas où on reçoit "Euro (EUR)" au lieu de "EUR"
        if (preg_match('/\(([A-Z]{3})\)/', $fromCurrency, $matches)) {
            $fromCurrency = $matches[1];
        }

        if ($fromCurrency === 'TND' || $amount <= 0) {
            return $amount;
        }

        try {
            $url = "https://v6.exchangerate-api.com/v6/{$this->apiKey}/pair/{$fromCurrency}/TND";
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            if (isset($data['result']) && $data['result'] === 'success') {
                return round($amount * $data['conversion_rate'], 3);
            }
        } catch (Exception $e) {
            // Logique de secours
        }

        // Taux manuels si l'API échoue
        $fallbacks = ['EUR' => 3.385, 'USD' => 3.120, 'GBP' => 3.920];
        $rate = $fallbacks[$fromCurrency] ?? 1.0;
        
        return round($amount * $rate, 3);
    }
}
