<?php
namespace App\Models;

class VeracidadModel extends BaseModel {
    protected $table = 'Veracidad';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}