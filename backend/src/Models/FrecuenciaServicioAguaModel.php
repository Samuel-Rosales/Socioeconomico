<?php
namespace App\Models;

class FrecuenciaServicioAguaModel extends BaseModel {
    protected $table = 'FrecuenciaServicioAgua';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}