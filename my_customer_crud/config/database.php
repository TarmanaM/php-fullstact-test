<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));

$dotenv->load();

$dsn = "pgsql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']};";
try {
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$redis = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => $_ENV['REDIS_HOST'],
    'port'   => $_ENV['REDIS_PORT'],
]);
?>
