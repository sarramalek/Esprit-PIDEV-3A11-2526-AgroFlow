<?php

use App\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$kernel = new Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();

return $container->get('doctrine')->getManager();

