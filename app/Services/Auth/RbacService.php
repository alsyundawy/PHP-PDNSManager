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
    public function hasPermission(User $user, string $permission): bool
    {
        if (in_array('admin', $this->getUserRoles($user))) {
            return true;
        }
        $permissions = $this->getUserPermissions($user);
        return in_array($permission, $permissions);
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
