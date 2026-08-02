<?php
declare(strict_types=1);
namespace App\Repositories;
use App\Core\Database;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private Database $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    public function find(int $id): ?User
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM users WHERE id = :id AND is_active = 1', ['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? $this->hydrate($data) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
    public function findByEmail(string $email): ?User
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM users WHERE email = :email AND is_active = 1', ['email' => $email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? $this->hydrate($data) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
    public function findByUsername(string $username): ?User
    {
        try {
            $stmt = $this->db->execute('SELECT * FROM users WHERE username = :username AND is_active = 1', ['username' => $username]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? $this->hydrate($data) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
    public function create(array $data): User
    {
        $sql = 'INSERT INTO users (username, email, password_hash, is_active, created_at, updated_at)
                VALUES (:username, :email, :password_hash, :is_active, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)';
        $this->db->execute($sql, [
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'is_active' => $data['is_active'] ?? 1,
        ]);
        $id = $this->db->lastInsertId();
        return $this->find((int) $id);
    }
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        if (empty($fields)) {
            return false;
        }
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $this->db->execute($sql, $params);
        return true;
    }
    public function delete(int $id): bool
    {
        return $this->update($id, ['is_active' => 0]);
    }
    public function getUsersWithRoles(int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT u.*, GROUP_CONCAT(r.name) as roles
                FROM users u
                LEFT JOIN user_role ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE u.is_active = 1
                GROUP BY u.id
                LIMIT :limit OFFSET :offset';
        $stmt = $this->db->execute($sql, ['limit' => $limit, 'offset' => $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function recordLogin(int $userId): void
    {
        $this->db->execute('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id', ['id' => $userId]);
    }
    private function hydrate(array $data): User
    {
        $user = new User();
        $user->id = (int) $data['id'];
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->is_active = (bool) $data['is_active'];
        $user->totp_secret = $data['totp_secret'] ?? null;
        $user->last_login = $data['last_login'] ? new \DateTimeImmutable($data['last_login']) : null;
        $user->created_at = new \DateTimeImmutable($data['created_at']);
        $user->updated_at = new \DateTimeImmutable($data['updated_at']);
        return $user;
    }
}
