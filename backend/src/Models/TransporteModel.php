<?php
namespace App\Models;

class TransporteModel extends BaseModel {
    protected $table = 'Transporte';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}