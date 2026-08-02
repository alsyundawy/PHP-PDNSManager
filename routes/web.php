<?php
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ZoneController;
use App\Controllers\RecordController;
use App\Controllers\DNSSECController;

/** @var \App\Core\Router $router */
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/zones', [ZoneController::class, 'index']);
$router->get('/zones/create', [ZoneController::class, 'create']);
$router->post('/zones', [ZoneController::class, 'create']);
$router->get('/zones/{id}', [ZoneController::class, 'show']);
$router->get('/zones/{id}/edit', [ZoneController::class, 'edit']);
$router->post('/zones/{id}/edit', [ZoneController::class, 'edit']);
$router->post('/zones/{id}/delete', [ZoneController::class, 'delete']);
$router->get('/zones/{id}/clone', [ZoneController::class, 'clone']);
$router->post('/zones/{id}/clone', [ZoneController::class, 'clone']);
$router->get('/zones/{id}/check', [ZoneController::class, 'check']);
$router->get('/zones/{id}/export', [ZoneController::class, 'export']);

$router->post('/zones/{zoneId}/records', [RecordController::class, 'store']);
$router->post('/zones/{zoneId}/records/{recordName}/{recordType}/update', [RecordController::class, 'update']);
$router->post('/zones/{zoneId}/records/{recordName}/{recordType}/delete', [RecordController::class, 'delete']);
$router->post('/zones/{zoneId}/records/bulk', [RecordController::class, 'bulk']);

$router->post('/zones/{zoneId}/dnssec/enable', [DNSSECController::class, 'enable']);
$router->post('/zones/{zoneId}/dnssec/disable', [DNSSECController::class, 'disable']);
$router->post('/zones/{zoneId}/dnssec/keys', [DNSSECController::class, 'createKey']);
$router->post('/zones/{zoneId}/dnssec/keys/{keyId}/delete', [DNSSECController::class, 'deleteKey']);
$router->post('/zones/{zoneId}/dnssec/keys/{keyId}/activate', [DNSSECController::class, 'activateKey']);
$router->post('/zones/{zoneId}/dnssec/keys/{keyId}/deactivate', [DNSSECController::class, 'deactivateKey']);
