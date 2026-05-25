<?php
namespace App\Models;

class TenenciaViviendaModel extends BaseModel {
    protected $table = 'TenenciaVivienda';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}