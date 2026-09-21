<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\AuditLog;

interface AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog;
    public function getByUser(int $userId, int $limit = 20): array;
    public function getRecent(int $limit = 20): array;
    public function search(array $filters = [], int $limit = 50, int $offset = 0): array;
    public function count(array $filters = []): int;
}
