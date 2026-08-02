<?php
declare(strict_types=1);
namespace Tests\Integration;
use PHPUnit\Framework\TestCase;
use App\Core\Database;
use PDO;

class DatabaseTest extends TestCase
{
    private Database $db;
    protected function setUp(): void {
        $config = ['default'=>'mysql','connections'=>['mysql'=>['driver'=>'sqlite','database'=>':memory:','host'=>'localhost','port'=>3306,'username'=>'test','password'=>'test','charset'=>'utf8mb4']]];
        $this->db = new Database($config);
    }
    public function testExecute(): void {
        $this->db->execute('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');
        $this->db->execute('INSERT INTO test (name) VALUES (:name)', ['name' => 'test']);
        $stmt = $this->db->execute('SELECT * FROM test');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('test', $result['name']);
    }
}
