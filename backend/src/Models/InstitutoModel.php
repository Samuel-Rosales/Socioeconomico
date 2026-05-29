<?php

namespace App\Models;

class InstitutoModel extends BaseModel
{
    protected $table = 'Instituto';

    public function getEstadoEncuestas()
    {
        $sql = "SELECT siglas, encuesta_activa FROM {$this->table} WHERE activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $map[strtolower($row['siglas'])] = (bool)(int)$row['encuesta_activa'];
        }
        return $map;
    }

    public function toggleEncuestaActiva($id)
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET encuesta_activa = NOT encuesta_activa WHERE id = :id AND activo = 1"
        );
        $stmt->execute(['id' => (int)$id]);
        return $stmt->rowCount() > 0;
    }

    public function getEncuestaActivaById($id)
    {
        $sql = "SELECT encuesta_activa FROM {$this->table} WHERE id = :id AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => (int)$id]);
        $row = $stmt->fetch();
        return $row ? (bool)(int)$row['encuesta_activa'] : null;
    }

    public function getEncuestaActivaBySiglas($siglas)
    {
        $sql = "SELECT encuesta_activa FROM {$this->table} WHERE LOWER(siglas) = LOWER(:siglas) AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['siglas' => $siglas]);
        $row = $stmt->fetch();
        return $row ? (bool)(int)$row['encuesta_activa'] : null;
    }

    public function getAllConEstadoEncuesta()
    {
        $sql = "SELECT id, siglas, nombre, encuesta_activa FROM {$this->table} WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
