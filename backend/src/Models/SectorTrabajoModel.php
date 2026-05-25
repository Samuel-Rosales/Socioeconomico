<?php
namespace App\Models;

class SectorTrabajoModel extends BaseModel {
    protected $table = 'SectorTrabajo';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}
