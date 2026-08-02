<?php
declare(strict_types=1);
namespace App\Repositories;
use App\Core\Database;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use PDO;

class RoleRepository implements RoleRepositoryInterface
{
    private Database $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    public function getRolesForUser(int $userId): array
    {
        $stmt = $this->db->execute(
            'SELECT r.name FROM roles r JOIN user_role ur ON r.id = ur.role_id WHERE ur.user_id = :user_id',
            ['user_id' => $userId]
        );
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    }
    public function getPermissionsForUser(int $userId): array
    {
        $stmt = $this->db->execute(
            'SELECT DISTINCT p.name FROM permissions p
             JOIN permission_role pr ON p.id = pr.permission_id
             JOIN roles r ON pr.role_id = r.id
             JOIN user_role ur ON r.id = ur.role_id
             WHERE ur.user_id = :user_id',
            ['user_id' => $userId]
        );
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    }
    public function findByName(string $name): ?Role
    {
        $stmt = $this->db->execute('SELECT * FROM roles WHERE name = :name', ['name' => $name]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        $role = new Role();
        $role->id = (int) $data['id'];
        $role->name = $data['name'];
        $role->description = $data['description'] ?? '';
        return $role;
    }
}
