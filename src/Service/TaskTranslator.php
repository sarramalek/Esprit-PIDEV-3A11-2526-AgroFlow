<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TaskTranslator
{
    private string $cacheFile;

    // Serveurs LibreTranslate publics en fallback
    private array $servers = [
        'https://libretranslate.com/translate',
        'https://translate.argosopentech.com/translate',
        'https://translate.terraprint.co/translate',
    ];

    public function __construct(private HttpClientInterface $client, string $projectDir)
    {
        $this->cacheFile = $projectDir . '/var/task_translations.json';
    }

    public function translate(string $text, string $lang): string
    {
        if ($lang === 'fr' || empty(trim($text))) return $text;

        $cache = $this->loadCache();
        $key   = md5($text . $lang);

        if (isset($cache[$key])) return $cache[$key];

        foreach ($this->servers as $server) {
            try {
                $response = $this->client->request('POST', $server, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'json'    => [
                        'q'      => $text,
                        'source' => 'fr',
                        'target' => $lang,
                        'format' => 'text',
                    ],
                    'timeout' => 6,
                ]);

                $data       = $response->toArray();
                $translated = $data['translatedText'] ?? null;

                if ($translated && $translated !== $text) {
                    $cache[$key] = $translated;
                    $this->saveCache($cache);
                    return $translated;
                }
            } catch (\Exception $e) {
                continue; // essayer le serveur suivant
            }
        }

        return $text; // fallback : texte original
    }

    private function loadCache(): array
    {
        if (!file_exists($this->cacheFile)) return [];
        return json_decode(file_get_contents($this->cacheFile), true) ?? [];
    }

    private function saveCache(array $data): void
    {
        file_put_contents(
            $this->cacheFile,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}