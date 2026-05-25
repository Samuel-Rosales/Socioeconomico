<?php
namespace App\Models;

class EstadoCivilModel extends BaseModel {
    protected $table = 'EstadoCivil';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}
