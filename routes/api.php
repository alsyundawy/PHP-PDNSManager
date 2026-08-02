<?php
use App\Controllers\Api\V1\ZoneApiController;
use App\Controllers\Api\V1\RecordApiController;
use App\Controllers\Api\V1\DNSSECApiController;

/** @var \App\Core\Router $router */
$router->group(['prefix' => '/api/v1', 'middleware' => ['auth', 'rbac']], function ($router) {
    $router->get('/zones', [ZoneApiController::class, 'index']);
    $router->post('/zones', [ZoneApiController::class, 'store']);
    $router->get('/zones/{id}', [ZoneApiController::class, 'show']);
    $router->put('/zones/{id}', [ZoneApiController::class, 'update']);
    $router->delete('/zones/{id}', [ZoneApiController::class, 'destroy']);
    $router->post('/zones/{id}/clone', [ZoneApiController::class, 'clone']);
    $router->get('/zones/{id}/check', [ZoneApiController::class, 'check']);
    $router->get('/zones/{id}/export', [ZoneApiController::class, 'export']);

    $router->get('/zones/{zoneId}/records', [RecordApiController::class, 'index']);
    $router->post('/zones/{zoneId}/records', [RecordApiController::class, 'store']);
    $router->put('/zones/{zoneId}/records/{recordName}/{recordType}', [RecordApiController::class, 'update']);
    $router->delete('/zones/{zoneId}/records/{recordName}/{recordType}', [RecordApiController::class, 'destroy']);
    $router->post('/zones/{zoneId}/records/bulk', [RecordApiController::class, 'bulk']);

    $router->post('/zones/{zoneId}/dnssec/enable', [DNSSECApiController::class, 'enable']);
    $router->post('/zones/{zoneId}/dnssec/disable', [DNSSECApiController::class, 'disable']);
    $router->get('/zones/{zoneId}/dnssec/keys', [DNSSECApiController::class, 'keys']);
    $router->post('/zones/{zoneId}/dnssec/keys', [DNSSECApiController::class, 'createKey']);
    $router->delete('/zones/{zoneId}/dnssec/keys/{keyId}', [DNSSECApiController::class, 'deleteKey']);
});
