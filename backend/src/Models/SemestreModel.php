<?php
namespace App\Models;

class SemestreModel extends BaseModel {
    protected $table = 'Semestre';

    public function create($nombre, $numero) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre, numero) VALUES (:nombre, :numero)");
        return $stmt->execute(['nombre' => $nombre, 'numero' => $numero]);
    }

    public function update($id, $nombre, $numero) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET nombre = :nombre, numero = :numero WHERE id = :id");
        return $stmt->execute(['id' => $id, 'nombre' => $nombre, 'numero' => $numero]);
    }
}