<?php

use App\Controllers\Api\V1\ZoneApiController;
use App\Controllers\Api\V1\RecordApiController;
use App\Controllers\Api\V1\DNSSECApiController;
use App\Controllers\Api\V1\ServerApiController;
use App\Controllers\Api\V1\ZoneTemplateApiController;
use App\Controllers\Api\V2\GraphQLController;
use App\Controllers\HealthController;

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

    // Multi-Server Cluster API
    $router->get('/servers', [ServerApiController::class, 'index']);
    $router->post('/servers', [ServerApiController::class, 'store']);
    $router->post('/servers/test', [ServerApiController::class, 'test']);
    $router->get('/servers/{id}', [ServerApiController::class, 'show']);
    $router->put('/servers/{id}', [ServerApiController::class, 'update']);
    $router->delete('/servers/{id}', [ServerApiController::class, 'destroy']);

    // Zone Templates API
    $router->get('/templates', [ZoneTemplateApiController::class, 'index']);
    $router->post('/templates', [ZoneTemplateApiController::class, 'store']);
    $router->get('/templates/{id}', [ZoneTemplateApiController::class, 'show']);
    $router->post('/templates/{id}/apply', [ZoneTemplateApiController::class, 'apply']);
    $router->delete('/templates/{id}', [ZoneTemplateApiController::class, 'destroy']);
});

// Health check API
$router->get('/api/v1/health', [HealthController::class, 'index']);

// Milestone v2.0.0: GraphQL Endpoint
$router->get('/graphql', [GraphQLController::class, 'handle']);
$router->post('/graphql', [GraphQLController::class, 'handle']);
