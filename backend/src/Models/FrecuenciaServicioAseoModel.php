<?php
namespace App\Models;

class FrecuenciaServicioAseoModel extends BaseModel {
    protected $table = 'FrecuenciaServicioAseo';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}