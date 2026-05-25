<?php
namespace App\Models;

class TipoEmpresaModel extends BaseModel {
    protected $table = 'TipoEmpresa';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}