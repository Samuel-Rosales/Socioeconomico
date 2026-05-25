<?php
namespace App\Models;

class FrecuenciaServicioGasModel extends BaseModel {
    protected $table = 'FrecuenciaServicioGas';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}