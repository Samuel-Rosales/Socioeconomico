<?php
namespace App\Models;

class NacionalidadModel extends BaseModel {
    protected $table = 'Nacionalidad';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }

    public function update($id, $nombre) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET nombore = :nombre WHERE id = :id");
        return $stmt->execute(['id' => $id, 'nombre'=> $nombre]);
    }
}