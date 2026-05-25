<?php
namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE activo = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND activo = 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET activo = 0 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function restore($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET activo = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}