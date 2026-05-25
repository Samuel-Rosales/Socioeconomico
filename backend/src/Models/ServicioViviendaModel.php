<?php
namespace App\Models;

class ServicioViviendaModel extends BaseModel {
    protected $table = 'ServicioVivienda';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}