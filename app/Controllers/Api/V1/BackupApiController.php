<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Core\Request;
use App\Core\Response;
use App\Services\Backup\BackupRestoreService;
use Exception;

class BackupApiController
{
    private BackupRestoreService $backupService;

    public function __construct(BackupRestoreService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index(Request $request): Response
    {
        $backups = $this->backupService->listBackups();
        return (new Response())->json($backups);
    }

    public function store(Request $request): Response
    {
        $passphrase = $request->input('passphrase') ? (string) $request->input('passphrase') : null;

        try {
            $backup = $this->backupService->createFullBackup($passphrase);
            return (new Response())->json($backup, 201);
        } catch (Exception $e) {
            return (new Response())->json(['error' => $e->getMessage()], 500);
        }
    }

    public function restore(Request $request): Response
    {
        $filename = (string) $request->input('filename', '');
        $passphrase = $request->input('passphrase') ? (string) $request->input('passphrase') : null;
        $dryRun = (bool) $request->input('dry_run', true);

        $backups = $this->backupService->listBackups();
        $target = null;

        foreach ($backups as $b) {
            if ($b['filename'] === basename($filename)) {
                $target = $b['filepath'];
                break;
            }
        }

        if ($target === null) {
            return (new Response())->json(['error' => 'Backup file not found'], 404);
        }

        try {
            $result = $this->backupService->restoreBackup($target, $passphrase, $dryRun);
            return (new Response())->json($result);
        } catch (Exception $e) {
            return (new Response())->json(['error' => $e->getMessage()], 422);
        }
    }
}
