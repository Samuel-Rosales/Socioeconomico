<?php
namespace App\Models;

class CategoriaOcupacionalModel extends BaseModel {
    protected $table = 'CategoriaOcupacional';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}