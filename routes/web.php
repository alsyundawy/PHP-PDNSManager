<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ZoneController;
use App\Controllers\RecordController;
use App\Controllers\AuditLogController;
use App\Controllers\DNSSECController;
use App\Controllers\HealthController;
use App\Controllers\ServerController;
use App\Controllers\ZoneTemplateController;
use App\Controllers\ZoneMetadataController;
use App\Controllers\TsigController;
use App\Controllers\DnsToolboxController;
use App\Controllers\BackupController;
use App\Controllers\SearchController;

/** @var \App\Core\Router $router */
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/locale/{lang}', [AuthController::class, 'setLocale']);

$router->get('/dashboard', [DashboardController::class, 'index']);

// Static /zones sub-paths before dynamic /zones/{id}
$router->get('/zones/import', [ZoneController::class, 'import']);
$router->post('/zones/import', [ZoneController::class, 'import']);
$router->get('/zones/bulk-records', [ZoneController::class, 'bulk']);
$router->post('/zones/bulk-records', [ZoneController::class, 'bulk']);
$router->get('/zones/reverse-wizard', [ZoneController::class, 'reverseWizard']);
$router->post('/zones/reverse-wizard', [ZoneController::class, 'reverseWizard']);

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
$router->get('/zones/{id}/export-bind', [ZoneController::class, 'exportBind']);

// Zone Operations & Replication
$router->post('/zones/{id}/notify', [ZoneController::class, 'notify']);
$router->post('/zones/{id}/axfr-retrieve', [ZoneController::class, 'axfrRetrieve']);
$router->post('/zones/{id}/rectify', [ZoneController::class, 'rectify']);

// Zone Metadata Management
$router->get('/zones/{id}/metadata', [ZoneMetadataController::class, 'index']);
$router->post('/zones/{id}/metadata', [ZoneMetadataController::class, 'update']);
$router->post('/zones/{id}/metadata/{kind}/delete', [ZoneMetadataController::class, 'delete']);

// TSIG Keys Management
$router->get('/tsig-keys', [TsigController::class, 'index']);
$router->post('/tsig-keys', [TsigController::class, 'create']);
$router->post('/tsig-keys/{id}/delete', [TsigController::class, 'delete']);

// DNS Security & Diagnostic Toolbox
$router->get('/toolbox', [DnsToolboxController::class, 'index']);
$router->post('/toolbox', [DnsToolboxController::class, 'index']);

// Cluster Backups & Restore
$router->get('/backups', [BackupController::class, 'index']);
$router->post('/backups', [BackupController::class, 'create']);
$router->get('/backups/{filename}/download', [BackupController::class, 'download']);
$router->post('/backups/restore', [BackupController::class, 'restore']);
$router->post('/backups/{filename}/delete', [BackupController::class, 'delete']);

// Async Search for Command Palette (Ctrl+K)
$router->get('/api/search', [SearchController::class, 'search']);

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

// Server Clusters
$router->get('/servers', [ServerController::class, 'index']);
$router->get('/servers/create', [ServerController::class, 'create']);
$router->post('/servers/create', [ServerController::class, 'create']);
$router->get('/servers/{id}/edit', [ServerController::class, 'edit']);
$router->post('/servers/{id}/edit', [ServerController::class, 'edit']);
$router->post('/servers/{id}/delete', [ServerController::class, 'delete']);
$router->post('/servers/test', [ServerController::class, 'test']);

// Zone Templates
$router->get('/templates', [ZoneTemplateController::class, 'index']);
$router->get('/templates/create', [ZoneTemplateController::class, 'create']);
$router->post('/templates/create', [ZoneTemplateController::class, 'create']);
$router->post('/templates/apply', [ZoneTemplateController::class, 'apply']);
$router->post('/templates/{id}/delete', [ZoneTemplateController::class, 'delete']);

// Health Monitoring
$router->get('/health', [HealthController::class, 'index']);

// Audit Trail & Logs
$router->get('/audit-logs', [AuditLogController::class, 'index']);
$router->get('/audit-logs/export', [AuditLogController::class, 'export']);
