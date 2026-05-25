<?php
namespace App\Models;

class TipoEstudianteModel extends BaseModel {
    protected $table = 'TipoEstudiante';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}