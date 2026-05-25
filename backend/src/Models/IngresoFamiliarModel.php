<?php
namespace App\Models;

class IngresoFamiliarModel extends BaseModel {
    protected $table = 'IngresoFamiliar';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}