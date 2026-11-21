<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\Backup\BackupRestoreService;
use RuntimeException;

class BackupController
{
    private const BACKUPS_URI = '/backups';
    private BackupRestoreService $backupService;

    public function __construct(BackupRestoreService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index(Request $request): Response
    {
        $backups = $this->backupService->listBackups();

        $html = view('backup.index', [
            'backups' => $backups,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);

        return (new Response())->html($html);
    }

    public function create(Request $request): Response
    {
        $passphrase = $request->input('passphrase') ? (string) $request->input('passphrase') : null;

        try {
            $this->backupService->createFullBackup($passphrase);
        } catch (RuntimeException $e) {
            // Flash error
        }

        return (new Response())->redirect(self::BACKUPS_URI);
    }

    public function download(Request $request, string $filename): Response
    {
        $cleanName = basename($filename);
        $backups = $this->backupService->listBackups();
        $target = null;

        foreach ($backups as $b) {
            if ($b['filename'] === $cleanName) {
                $target = $b['filepath'];
                break;
            }
        }

        if ($target === null || !file_exists($target)) {
            return (new Response())->redirect(self::BACKUPS_URI);
        }

        $content = (string) file_get_contents($target);
        $contentType = str_ends_with($cleanName, '.enc') ? 'application/octet-stream' : 'application/json';

        $response = new Response(200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $cleanName . '"',
            'Content-Length' => (string) strlen($content),
        ]);
        $response->getBody()->write($content);

        return $response;
    }

    public function restore(Request $request): Response
    {
        $filename = (string) $request->input('filename', '');
        $passphrase = $request->input('passphrase') ? (string) $request->input('passphrase') : null;
        $dryRun = (bool) $request->input('dry_run', true);

        $cleanName = basename($filename);
        $backups = $this->backupService->listBackups();
        $target = null;

        foreach ($backups as $b) {
            if ($b['filename'] === $cleanName) {
                $target = $b['filepath'];
                break;
            }
        }

        if ($target === null) {
            return (new Response())->redirect(self::BACKUPS_URI);
        }

        $result = null;
        $error = null;

        try {
            $result = $this->backupService->restoreBackup($target, $passphrase, $dryRun);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $backups = $this->backupService->listBackups();
        $html = view('backup.index', [
            'backups' => $backups,
            'restoreResult' => $result,
            'restoreError' => $error,
            'user' => $request->getAttribute('user'),
            'csrfToken' => csrf_token(),
        ]);

        return (new Response())->html($html);
    }

    public function delete(Request $request, string $filename): Response
    {
        $this->backupService->deleteBackup($filename);
        return (new Response())->redirect(self::BACKUPS_URI);
    }
}
