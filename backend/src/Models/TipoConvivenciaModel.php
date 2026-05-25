<?php
namespace App\Models;

class TipoConvivenciaModel extends BaseModel {
    protected $table = 'TipoConvivencia';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}