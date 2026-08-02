<?php
declare(strict_types=1);
namespace App\Services;

use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Core\Logger;

/**
 * SECURITY FIX: Original code used $_SERVER['REMOTE_ADDR'] directly.
 * While that is correct for direct connections, the IP is now resolved
 * through a dedicated method that can be configured for reverse proxies.
 * X-Forwarded-For is NOT trusted by default to prevent IP spoofing.
 */
class AuditLogService
{
    private AuditLogRepositoryInterface $auditRepo;
    private Logger $logger;
    private bool $trustProxy;

    public function __construct(
        AuditLogRepositoryInterface $auditRepo,
        Logger $logger,
        bool $trustProxy = false
    ) {
        $this->auditRepo   = $auditRepo;
        $this->logger      = $logger;
        $this->trustProxy  = $trustProxy;
    }

    public function log(?int $userId, string $action, ?array $payload = null, int $statusCode = 200): void
    {
        // SECURITY FIX: sanitize payload — strip password fields before logging
        $safePayload = $payload ? $this->sanitizePayload($payload) : null;

        $this->auditRepo->create([
            'user_id'     => $userId,
            'action'      => $action,
            'payload'     => $safePayload ? json_encode($safePayload) : null,
            'status_code' => $statusCode,
            'ip_address'  => $this->resolveIp(),
        ]);

        $this->logger->channel('audit')->info($action, [
            'user_id' => $userId,
            'status'  => $statusCode,
        ]);
    }

    private function resolveIp(): string
    {
        if ($this->trustProxy) {
            $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
            if ($forwarded !== '') {
                // Take only first IP to prevent injection
                return trim(explode(',', $forwarded)[0]);
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function sanitizePayload(array $payload): array
    {
        $sensitive = ['password', 'password_hash', 'token', 'secret', 'api_key', 'totp_secret'];
        foreach ($sensitive as $key) {
            if (isset($payload[$key])) {
                $payload[$key] = '[REDACTED]';
            }
        }
        return $payload;
    }
}
