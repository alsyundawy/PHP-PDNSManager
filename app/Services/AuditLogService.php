<?php
declare(strict_types=1);
namespace App\Services;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Core\Logger;

class AuditLogService
{
    private AuditLogRepositoryInterface $auditRepo;
    private Logger $logger;
    public function __construct(
        AuditLogRepositoryInterface $auditRepo,
        Logger $logger
    ) {
        $this->auditRepo = $auditRepo;
        $this->logger = $logger;
    }
    public function log(?int $userId, string $action, ?array $payload = null, int $statusCode = 200): void
    {
        $this->auditRepo->create([
            'user_id' => $userId,
            'action' => $action,
            'payload' => $payload ? json_encode($payload) : null,
            'status_code' => $statusCode,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ]);
        $this->logger->channel('audit')->info($action, [
            'user_id' => $userId,
            'status' => $statusCode,
        ]);
    }
}
