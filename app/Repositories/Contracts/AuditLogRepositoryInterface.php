<?php
declare(strict_types=1);
namespace App\Repositories\Contracts;
use App\Models\AuditLog;

interface AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog;
    public function getByUser(int $userId, int $limit = 20): array;
    public function getRecent(int $limit = 20): array;
}
