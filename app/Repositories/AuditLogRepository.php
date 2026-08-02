<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Core\Database;
use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use PDO;

/**
 * BUGFIX: PDO does not support named placeholders for LIMIT in some drivers.
 * Using PDO::PARAM_INT binding or casting to int and embedding directly.
 * Both getByUser() and getRecent() are fixed.
 */
class AuditLogRepository implements AuditLogRepositoryInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function create(array $data): AuditLog
    {
        $sql = 'INSERT INTO audit_logs (user_id, action, payload, status_code, ip_address, created_at)
                VALUES (:user_id, :action, :payload, :status_code, :ip_address, NOW())';
        $this->db->execute($sql, [
            'user_id'     => $data['user_id'],
            'action'      => $data['action'],
            'payload'     => $data['payload'] ?? null,
            'status_code' => $data['status_code'] ?? 200,
            'ip_address'  => $data['ip_address'] ?? '0.0.0.0',
        ]);
        $id = $this->db->lastInsertId();
        return $this->find((int) $id);
    }

    public function getByUser(int $userId, int $limit = 20): array
    {
        // BUGFIX: Use intval cast in SQL string for LIMIT to avoid PDO binding issues
        $limit = max(1, min(100, $limit));
        $stmt  = $this->db->execute(
            'SELECT * FROM audit_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT ' . $limit,
            ['user_id' => $userId]
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecent(int $limit = 20): array
    {
        // BUGFIX: same LIMIT binding issue
        $limit = max(1, min(100, $limit));
        $stmt  = $this->db->execute(
            'SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT ' . $limit,
            []
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function find(int $id): AuditLog
    {
        $stmt = $this->db->execute('SELECT * FROM audit_logs WHERE id = :id', ['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            throw new \RuntimeException('Audit log not found after insert');
        }
        $log              = new AuditLog();
        $log->id          = (int) $data['id'];
        $log->user_id     = $data['user_id'] ? (int) $data['user_id'] : null;
        $log->action      = $data['action'];
        $log->payload     = $data['payload'] ? json_decode($data['payload'], true) : null;
        $log->status_code = (int) $data['status_code'];
        $log->ip_address  = $data['ip_address'];
        $log->created_at  = new \DateTimeImmutable($data['created_at']);
        return $log;
    }
}
