<?php
declare(strict_types=1);
namespace App\Repositories\Contracts;
use App\Models\Role;

interface RoleRepositoryInterface
{
    public function getRolesForUser(int $userId): array;
    public function getPermissionsForUser(int $userId): array;
    public function findByName(string $name): ?Role;
}
