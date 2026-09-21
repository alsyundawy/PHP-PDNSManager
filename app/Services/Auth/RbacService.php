<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;

class RbacService
{
    private UserRepositoryInterface $userRepo;
    private RoleRepositoryInterface $roleRepo;
    public function __construct(
        UserRepositoryInterface $userRepo,
        RoleRepositoryInterface $roleRepo
    ) {
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
    }
    public function hasPermission(User|int $user, string $permission): bool
    {
        $userObj = is_int($user) ? $this->userRepo->find($user) : $user;
        if (!$userObj || !$userObj->isActive()) {
            return false;
        }
        if (in_array('admin', $this->getUserRoles($userObj), true)) {
            return true;
        }
        $permissions = $this->getUserPermissions($userObj);
        return in_array($permission, $permissions, true);
    }
    public function getUser(int $userId): ?User
    {
        return $this->userRepo->find($userId);
    }
    private function getUserRoles(User $user): array
    {
        return $this->roleRepo->getRolesForUser($user->id);
    }
    private function getUserPermissions(User $user): array
    {
        return $this->roleRepo->getPermissionsForUser($user->id);
    }
}
