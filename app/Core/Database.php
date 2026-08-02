<?php
declare(strict_types=1);
namespace App\Core;
use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private array $config;
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }
    private function connect(): void
    {
        $default = $this->config['default'] ?? 'mysql';
        $connection = $this->config['connections'][$default] ?? throw new \InvalidArgumentException('Database connection not found');
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $connection['driver'],
            $connection['host'],
            $connection['port'],
            $connection['database'],
            $connection['charset']
        );
        try {
            $this->pdo = new PDO($dsn, $connection['username'], $connection['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
    public function prepare(string $sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }
    public function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }
    public function commit(): void
    {
        $this->pdo->commit();
    }
    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
