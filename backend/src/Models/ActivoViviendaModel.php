<?php
namespace App\Models;

class ActivoViviendaModel extends BaseModel {
    protected $table = 'ActivoVivienda';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}