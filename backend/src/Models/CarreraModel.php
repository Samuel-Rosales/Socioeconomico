<?php
namespace App\Models;

class CarreraModel extends BaseModel {
    protected $table = 'Carrera';

    public function getAllByInstituto($institutoId)
    {
        $sql = "SELECT c.*
                FROM Carrera c
                INNER JOIN Instituto_Carrera ic ON ic.carrera_id = c.id
                WHERE c.activo = 1
                  AND ic.activo = 1
                  AND ic.instituto_id = :instituto_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['instituto_id' => (int)$institutoId]);
        return $stmt->fetchAll();
    }

    public function create($nombre) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $nombre]);
    }
}