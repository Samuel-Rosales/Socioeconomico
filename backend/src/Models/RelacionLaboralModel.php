<?php
namespace App\Models;

class RelacionLaboralModel extends BaseModel {
    protected $table = 'RelacionLaboral';

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}
