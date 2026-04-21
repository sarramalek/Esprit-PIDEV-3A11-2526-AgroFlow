<?php

require_once 'vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;
use App\Service\SmartVisionService;

// Mock context for Symfony environments
$_ENV['GEMINI_API_KEY'] = 'AIzaSyDDJorBt4-nyBq_kxMY6btsK1SK_yBMg-k';

$client = HttpClient::create();
$service = new SmartVisionService($client, $_ENV['GEMINI_API_KEY']);

try {
    // Small dummy base64 image (1x1 white pixel)
    $base64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';
    $result = $service->analyzeImage($base64, 'image/png');
    print_r($result);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getResponse')) {
        echo "RESPONSE: " . $e->getResponse()->getContent(false) . "\n";
    }
}
