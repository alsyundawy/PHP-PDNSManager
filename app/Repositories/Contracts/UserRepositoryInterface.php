<?php
declare(strict_types=1);
namespace App\Repositories\Contracts;
use App\Models\User;

interface UserRepositoryInterface
{
    public function find(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByUsername(string $username): ?User;
    public function create(array $data): User;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getUsersWithRoles(int $limit = 20, int $offset = 0): array;
    public function recordLogin(int $userId): void;
}
