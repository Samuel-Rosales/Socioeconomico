<?php
namespace App\Models;

class AmbienteViviendaModel extends BaseModel {
    protected $table = 'AmbienteVivienda';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}