<?php

namespace App\Models;

class UsuarioModel extends BaseModel
{
    protected $table = 'Usuario';

    public function getAll()
    {
        $sql = "SELECT u.id, u.ci, u.nombre_completo, u.rol_id, u.instituto_id, u.activo, u.creado_at,
                       r.nombre AS rol_nombre,
                       r.codigo AS rol_codigo,
                       i.siglas AS instituto_siglas,
                       i.nombre AS instituto_nombre
                FROM Usuario u
                INNER JOIN Rol r ON r.id = u.rol_id
                LEFT JOIN Instituto i ON i.id = u.instituto_id
                WHERE u.activo = 1
                ORDER BY u.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllByInstituto($institutoId)
    {
        $sql = "SELECT u.id, u.ci, u.nombre_completo, u.rol_id, u.instituto_id, u.activo, u.creado_at,
                       r.nombre AS rol_nombre,
                       r.codigo AS rol_codigo,
                       i.siglas AS instituto_siglas,
                       i.nombre AS instituto_nombre
                FROM Usuario u
                INNER JOIN Rol r ON r.id = u.rol_id
                LEFT JOIN Instituto i ON i.id = u.instituto_id
                WHERE u.activo = 1
                  AND u.instituto_id = :instituto_id
                ORDER BY u.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['instituto_id' => (int)$institutoId]);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $sql = "SELECT u.id, u.ci, u.nombre_completo, u.rol_id, u.instituto_id, u.activo, u.creado_at,
                       r.nombre AS rol_nombre,
                       r.codigo AS rol_codigo,
                       i.siglas AS instituto_siglas,
                       i.nombre AS instituto_nombre
                FROM Usuario u
                INNER JOIN Rol r ON r.id = u.rol_id
                LEFT JOIN Instituto i ON i.id = u.instituto_id
                WHERE u.id = :id
                  AND u.activo = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch();
    }

    public function getByIdAndInstituto($id, $institutoId)
    {
        $sql = "SELECT u.id, u.ci, u.nombre_completo, u.rol_id, u.instituto_id, u.activo, u.creado_at,
                       r.nombre AS rol_nombre,
                       r.codigo AS rol_codigo,
                       i.siglas AS instituto_siglas,
                       i.nombre AS instituto_nombre
                FROM Usuario u
                INNER JOIN Rol r ON r.id = u.rol_id
                LEFT JOIN Instituto i ON i.id = u.instituto_id
                WHERE u.id = :id
                  AND u.activo = 1
                  AND u.instituto_id = :instituto_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => (int)$id,
            'instituto_id' => (int)$institutoId,
        ]);
        return $stmt->fetch();
    }

    public function getByIdRaw($id)
    {
        $sql = "SELECT u.id, u.ci, u.nombre_completo, u.rol_id, u.instituto_id, u.activo, u.creado_at,
                       r.nombre AS rol_nombre,
                       r.codigo AS rol_codigo,
                       i.siglas AS instituto_siglas,
                       i.nombre AS instituto_nombre
                FROM Usuario u
                INNER JOIN Rol r ON r.id = u.rol_id
                LEFT JOIN Instituto i ON i.id = u.instituto_id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $allowed = ['ci', 'nombre_completo', 'password', 'rol_id', 'instituto_id', 'activo'];
        $data = array_intersect_key($data, array_flip($allowed));

        $columns = array_keys($data);
        if (empty($columns)) {
            return 0;
        }

        $columnSql = implode(', ', $columns);
        $placeholderSql = ':' . implode(', :', $columns);

        $sql = "INSERT INTO {$this->table} ($columnSql) VALUES ($placeholderSql)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    public function updateById($id, array $data)
    {
        $allowed = ['ci', 'nombre_completo', 'password', 'rol_id', 'instituto_id', 'activo'];
        $data = array_intersect_key($data, array_flip($allowed));

        if (empty($data)) {
            return false;
        }

        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = :$column";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $data['id'] = (int)$id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function findByCiWithRoleAndInstituto($ci)
    {
        $sql = "SELECT u.id, u.ci, u.nombre_completo, u.password, u.rol_id, u.instituto_id, u.activo,
                       r.nombre AS rol_nombre,
                       r.codigo AS rol_codigo,
                       i.siglas AS instituto_siglas,
                       i.nombre AS instituto_nombre
                FROM Usuario u
                INNER JOIN Rol r ON r.id = u.rol_id
                LEFT JOIN Instituto i ON i.id = u.instituto_id
                WHERE u.ci = :ci
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ci' => (string)$ci]);
        return $stmt->fetch();
    }
}
