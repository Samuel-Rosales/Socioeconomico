<?php
namespace App\Models;

class SexoModel extends BaseModel {
    protected $table = 'Sexo';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}