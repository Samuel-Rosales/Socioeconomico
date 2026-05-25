<?php
namespace App\Models;

class NivelEducacionModel extends BaseModel {
    protected $table = 'NivelEducacion';

    public function create($nombre, $valor_estrato) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombre, valor_estrato) VALUES (:nombre, :valor_estrato)");
        return $stmt->execute([
            'nombre' => $nombre,
            'valor_estrato' => $valor_estrato
        ]);
    }
}