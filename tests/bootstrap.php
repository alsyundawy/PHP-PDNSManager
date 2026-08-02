<?php
declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env.testing');
$dotenv->load();
error_reporting(E_ALL);
ini_set('display_errors', '1');
