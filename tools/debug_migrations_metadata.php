<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\ComparatorConfig;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->load(__DIR__ . '/../.env');

$url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;
if (!is_string($url) || $url === '') {
    fwrite(STDERR, "DATABASE_URL is missing.\n");
    exit(1);
}

$conn = DriverManager::getConnection(['url' => $url]);
$schemaManager = $conn->createSchemaManager();

$expected = new Table('doctrine_migration_versions');
$expected->addColumn('version', 'string', ['notnull' => true, 'length' => 191]);
$expected->addColumn('executed_at', 'datetime', ['notnull' => false]);
$expected->addColumn('execution_time', 'integer', ['notnull' => false]);
$expected->setPrimaryKey(['version']);

if (class_exists(ComparatorConfig::class)) {
    $comparator = $schemaManager->createComparator((new ComparatorConfig())->withReportModifiedIndexes(false));
} else {
    $comparator = $schemaManager->createComparator();
}

$current = $schemaManager->introspectTable('doctrine_migration_versions');
$diff = $comparator->compareTables($current, $expected);

echo $diff->isEmpty() ? "DIFF: empty\n" : "DIFF: non-empty\n";
if (!$diff->isEmpty()) {
    var_dump($diff);
}

