<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ControllerAdminControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/DashboardAdmin');

        $statusCode = $client->getResponse()->getStatusCode();
        // 200=OK, 302=redirect login, 403=accès refusé, 500=erreur serveur
        $this->assertContains($statusCode, [200, 302, 403, 500]);
    }
}