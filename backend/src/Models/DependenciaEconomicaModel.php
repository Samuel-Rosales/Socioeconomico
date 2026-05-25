<?php
namespace App\Models;

class DependenciaEconomicaModel extends BaseModel {
    protected $table = 'DependenciaEconomica';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}