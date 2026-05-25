<?php
namespace App\Models;

class TipoBecaModel extends BaseModel {
    protected $table = 'TipoBeca';

    public function getAllByInstituto($institutoId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE activo = 1 AND instituto_id = :instituto_id");
        $stmt->execute(['instituto_id' => (int)$institutoId]);
        return $stmt->fetchAll();
    }

    public function create($nombre, $institutoId) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre, instituto_id) VALUES (:nombre, :instituto_id)");
        return $stmt->execute(['nombre' => $nombre, 'instituto_id' => (int)$institutoId]);
    }
}