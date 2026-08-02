<?php
declare(strict_types=1);
use App\Core\Application;
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$app = new Application(__DIR__ . '/../');
$app->run();
