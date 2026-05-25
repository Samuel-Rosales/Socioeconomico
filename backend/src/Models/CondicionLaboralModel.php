<?php
namespace App\Models;

class CondicionLaboralModel extends BaseModel {
    protected $table = 'CondicionLaboral';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}
