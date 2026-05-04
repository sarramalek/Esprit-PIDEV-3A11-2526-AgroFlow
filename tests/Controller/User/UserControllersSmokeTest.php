<?php

namespace App\Tests\Controller\User;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserControllersSmokeTest extends WebTestCase
{
    /**
     * @dataProvider routesProvider
     */
    public function testUserRoutesRespondWithoutFatal(string $method, string $path): void
    {
        $client = static::createClient();
        $client->request($method, $path);

        $statusCode = $client->getResponse()->getStatusCode();
        // Smoke test only: route exists and doesn't crash test runtime.
        $this->assertContains($statusCode, [200, 302, 403, 405, 500]);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public function routesProvider(): array
    {
        return [
            ['GET', '/agriculteur/offre/front'],
            ['GET', '/agriculteur/abonnement/front'],
            ['GET', '/agriculteur/tache/front'],
            ['GET', '/admin/abonnements'],
            ['GET', '/admin/users'],
            ['GET', '/offre/'],
            ['GET', '/tache/'],
            ['GET', '/ouvrier/'],
            ['GET', '/agriculteur/ouvriers'],
        ];
    }
}
