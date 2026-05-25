<?php
namespace App\Models;

use PDO;
use Exception;

class EncuestaModel extends BaseModel {
    protected $table = 'Encuesta';

    public function checkDuplicados($cedula = null, $email = null)
    {
        $cedula = is_string($cedula) ? trim($cedula) : (is_numeric($cedula) ? (string)$cedula : '');
        $email = is_string($email) ? trim($email) : '';

        $result = [
            'cedula_exists' => false,
            'email_exists' => false,
        ];

        if ($cedula === '' && $email === '') {
            return $result;
        }

        $where = [];
        $bindings = [];

        if ($cedula !== '') {
            $where[] = 'cedula = :cedula';
            $bindings['cedula'] = $cedula;
        }
        if ($email !== '') {
            $where[] = 'email = :email';
            $bindings['email'] = $email;
        }

        $sql = 'SELECT cedula, email FROM ' . $this->table . ' WHERE ' . implode(' OR ', $where) . ' LIMIT 20';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            if (!$result['cedula_exists'] && $cedula !== '' && isset($row['cedula']) && (string)$row['cedula'] === $cedula) {
                $result['cedula_exists'] = true;
            }
            if (!$result['email_exists'] && $email !== '' && isset($row['email']) && strcasecmp((string)$row['email'], $email) === 0) {
                $result['email_exists'] = true;
            }
        }

        return $result;
    }

    /**
     * Lista resumida de encuestas (para dashboards/tablas).
     *
     * Nota: esto sobrescribe el getAll() del BaseModel (polimorfismo) para evitar
     * un SELECT * sobre una tabla grande y para incluir joins/cálculos.
     */
    public function getAll()
    {
        $sql = $this->buildResumenSql();

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllByInstituto($institutoId)
    {
        $sql = $this->buildResumenSql([
            'withInstitutoFilter' => true,
        ]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'instituto_id' => (int)$institutoId,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Lista resumida paginada + filtros.
     *
     * Opciones soportadas (todas opcionales):
     * - q (string): busca por nombres/apellidos/cedula
     * - carrera_id (int)
    * - estrato (string|int): completa|pendiente|1..5
    * - estado (string): alias legacy de estrato (completa|pendiente)
     * - page (int)
     * - per_page (int)
     */
    public function getResumenPaginated($institutoId = null, array $options = [])
    {
        $page = isset($options['page']) && is_numeric($options['page']) ? (int)$options['page'] : 1;
        $perPage = isset($options['per_page']) && is_numeric($options['per_page']) ? (int)$options['per_page'] : 10;

        if ($page < 1) {
            $page = 1;
        }

        if ($perPage < 1) {
            $perPage = 10;
        }

        // Límite defensivo para evitar respuestas enormes por accidente.
        if ($perPage > 100) {
            $perPage = 100;
        }

        $withInstitutoFilter = !empty($institutoId);
        $extraWhere = [];
        $bindings = [];

        if ($withInstitutoFilter) {
            $bindings['instituto_id'] = (int)$institutoId;
        }

        if (isset($options['carrera_id']) && is_numeric($options['carrera_id']) && (int)$options['carrera_id'] > 0) {
            $extraWhere[] = 'e.carrera_id = :carrera_id';
            $bindings['carrera_id'] = (int)$options['carrera_id'];
        }

        // Estrato/estado (derivados)
        $estratoParam = null;
        if (isset($options['estrato'])) {
            $estratoParam = $options['estrato'];
        } elseif (isset($options['estado'])) {
            // Compatibilidad: versiones previas enviaban "estado".
            $estratoParam = $options['estado'];
        }

        if ($estratoParam !== null) {
            $estratoRaw = strtolower(trim((string)$estratoParam));
            $esCompletaSql = '(e.tipo_vivienda_id IS NOT NULL AND e.fuente_ingreso_familiar_id IS NOT NULL AND e.nivel_eduacion_padre_id IS NOT NULL AND e.nivel_eduacion_madre_id IS NOT NULL)';
            $esIncompletaSql = '(e.tipo_vivienda_id IS NULL OR e.fuente_ingreso_familiar_id IS NULL OR e.nivel_eduacion_padre_id IS NULL OR e.nivel_eduacion_madre_id IS NULL)';

            // Para filtros numéricos 1..5 necesitamos el puntaje (requiere JOINs, por eso el COUNT usa los mismos LEFT JOIN).
            $puntajeSql = '(COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0))';

            if ($estratoRaw === 'completa') {
                $extraWhere[] = $esCompletaSql;
            } elseif ($estratoRaw === 'pendiente' || $estratoRaw === 'incompleta') {
                $extraWhere[] = $esIncompletaSql;
            } elseif (is_numeric($estratoRaw)) {
                $estratoNum = (int)$estratoRaw;
                if ($estratoNum >= 1 && $estratoNum <= 5) {
                    $extraWhere[] = $esCompletaSql;

                    if ($estratoNum === 1) {
                        $extraWhere[] = "$puntajeSql <= 6";
                    } elseif ($estratoNum === 2) {
                        $extraWhere[] = "$puntajeSql > 6 AND $puntajeSql <= 9";
                    } elseif ($estratoNum === 3) {
                        $extraWhere[] = "$puntajeSql > 9 AND $puntajeSql <= 12";
                    } elseif ($estratoNum === 4) {
                        $extraWhere[] = "$puntajeSql > 12 AND $puntajeSql <= 16";
                    } else {
                        $extraWhere[] = "$puntajeSql > 16";
                    }
                }
            }
        }

        if (isset($options['q'])) {
            $q = trim((string)$options['q']);
            if ($q !== '') {
                $tokens = preg_split('/\s+/', $q);
                $tokens = is_array($tokens) ? array_values(array_filter(array_map('trim', $tokens), function ($token) {
                    return $token !== '';
                })) : [];

                if (!empty($tokens)) {
                    $searchParts = [];
                    foreach ($tokens as $idx => $token) {
                        $paramName = 'q' . $idx;
                        $searchParts[] = "(e.nombres LIKE :{$paramName} OR e.apellidos LIKE :{$paramName} OR CONCAT(e.nombres, ' ', e.apellidos) LIKE :{$paramName} OR e.cedula LIKE :{$paramName})";
                        $bindings[$paramName] = '%' . $token . '%';
                    }

                    $extraWhere[] = '(' . implode(' AND ', $searchParts) . ')';
                }
            }
        }

        // Conteo total (para paginación)
        // Nota: usamos los mismos LEFT JOIN que el resumen para soportar filtros que dependan del puntaje/estrato.
        $countSql = "SELECT COUNT(*) AS total
            FROM Encuesta e
            LEFT JOIN Carrera c ON c.id = e.carrera_id
            LEFT JOIN Instituto i ON i.id = e.instituto_id
            LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
            LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
            LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
            LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
            WHERE e.activo = 1";
        if ($withInstitutoFilter) {
            $countSql .= ' AND e.instituto_id = :instituto_id';
        }
        foreach ($extraWhere as $clause) {
            $countSql .= ' AND ' . $clause;
        }

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($bindings);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = (int)ceil($total / $perPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        if ($offset < 0) {
            $offset = 0;
        }

        $sql = $this->buildResumenSql([
            'withInstitutoFilter' => $withInstitutoFilter,
            'extraWhere' => $extraWhere,
            'limit' => $perPage,
            'offset' => $offset,
        ]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'pagination' => [
                'page' => (int)$page,
                'per_page' => (int)$perPage,
                'total' => (int)$total,
                'total_pages' => (int)$totalPages,
            ],
        ];
    }

    private function buildResumenSql(array $options = [])
    {
        $withInstitutoFilter = !empty($options['withInstitutoFilter']);
        $extraWhere = isset($options['extraWhere']) && is_array($options['extraWhere']) ? $options['extraWhere'] : [];
        $limit = isset($options['limit']) && is_numeric($options['limit']) ? (int)$options['limit'] : null;
        $offset = isset($options['offset']) && is_numeric($options['offset']) ? (int)$options['offset'] : null;

        // Estrato (Méndez-Castellano / Graffar)
        // Se calcula a partir de los catálogos que tienen valor_estrato:
        // - TipoVivienda
        // - FuenteIngresoFamiliar
        // - NivelEducacion (padre y madre)
        // Puntaje total (4 variables) => rango típico 4..20.
        $puntajeSql = "(COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0))";
        $puntajeCompletoSql = "(e.tipo_vivienda_id IS NOT NULL AND e.fuente_ingreso_familiar_id IS NOT NULL AND e.nivel_eduacion_padre_id IS NOT NULL AND e.nivel_eduacion_madre_id IS NOT NULL)";

        $sql = "SELECT
                    e.id,
                    e.creado,
                    e.nombres,
                    e.apellidos,
                    CONCAT(e.nombres, ' ', e.apellidos) AS estudiante,
                    e.cedula,
                    e.carrera_id,
                    c.nombre AS carrera,
                    e.instituto_id,
                    i.siglas AS instituto_siglas,
                    i.nombre AS instituto_nombre,

                    CASE
                        WHEN $puntajeCompletoSql THEN $puntajeSql
                        ELSE NULL
                    END AS estrato_puntaje,

                    CASE
                        WHEN $puntajeCompletoSql THEN
                            CASE
                                WHEN $puntajeSql <= 6 THEN 1
                                WHEN $puntajeSql <= 9 THEN 2
                                WHEN $puntajeSql <= 12 THEN 3
                                WHEN $puntajeSql <= 16 THEN 4
                                ELSE 5
                            END
                        ELSE NULL
                    END AS estrato

                FROM Encuesta e
                LEFT JOIN Carrera c ON c.id = e.carrera_id
                LEFT JOIN Instituto i ON i.id = e.instituto_id
                LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
                LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
                LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
                LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
                WHERE e.activo = 1";

        if ($withInstitutoFilter) {
            $sql .= " AND e.instituto_id = :instituto_id";
        }

        foreach ($extraWhere as $clause) {
            if (is_string($clause) && trim($clause) !== '') {
                $sql .= ' AND ' . $clause;
            }
        }

        $sql .= " ORDER BY e.id DESC";

        if ($limit !== null && $offset !== null) {
            if ($limit < 1) {
                $limit = 1;
            }
            if ($offset < 0) {
                $offset = 0;
            }
            $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        }

        return $sql;
    }

    /**
     * Guarda la encuesta completa usando una Transacción
     * Las transacciones aseguran que si algo falla, no se guarde nada a medias.
     */
    public function guardarCompleta(array $datos, array $relaciones) {
        try {
            $this->db->beginTransaction();

            // 1. Insertar en la tabla principal 'Encuesta'
            $encuestaId = $this->insertarPrincipal($datos);

            // 2. Insertar relaciones Many-to-Many (Activos, Servicios, Ambientes)
            $this->guardarRelaciones($encuestaId, $relaciones);

            $this->db->commit();
            return $encuestaId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function insertarPrincipal(array $datos) {
        // Filtramos solo los campos que existen en la tabla para seguridad
        // En una app real, podrías automatizar esto con un describe de la tabla
        $columnas = implode(', ', array_keys($datos));
        $placeholders = ':' . implode(', :', array_keys($datos));

        $sql = "INSERT INTO {$this->table} ($columnas) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($datos);

        return $this->db->lastInsertId();
    }

    private function guardarRelaciones($encuestaId, array $relaciones) {
        // Relación: Activos de Vivienda
        if (!empty($relaciones['activos'])) {
            foreach ($relaciones['activos'] as $activoId) {
                $stmt = $this->db->prepare("INSERT INTO ConjuntoActivoVivienda (encuesta_id, activo_vivienda_id) VALUES (?, ?)");
                $stmt->execute([$encuestaId, $activoId]);
            }
        }

        // Relación: Servicios de Vivienda
        if (!empty($relaciones['servicios'])) {
            foreach ($relaciones['servicios'] as $servicioId) {
                $stmt = $this->db->prepare("INSERT INTO ConjuntoServicioVivienda (encuesta_id, servicio_vivienda_id) VALUES (?, ?)");
                $stmt->execute([$encuestaId, $servicioId]);
            }
        }

        // Relación: Ambientes de Vivienda
        if (!empty($relaciones['ambientes'])) {
            foreach ($relaciones['ambientes'] as $ambienteId) {
                $stmt = $this->db->prepare("INSERT INTO ConjuntoAmbienteVivienda (encuesta_id, ambiente_vivienda_id) VALUES (?, ?)");
                $stmt->execute([$encuestaId, $ambienteId]);
            }
        }
    }

    public function obtenerDetalleCompleto($id, $institutoId = null)
    {
        $puntajeSql = "(COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0))";
        $puntajeCompletoSql = "(e.tipo_vivienda_id IS NOT NULL AND e.fuente_ingreso_familiar_id IS NOT NULL AND e.nivel_eduacion_padre_id IS NOT NULL AND e.nivel_eduacion_madre_id IS NOT NULL)";

        $sql = "SELECT
                    e.*,

                    i.siglas AS instituto_siglas,
                    i.nombre AS instituto_nombre,

                    n.nombre AS nacionalidad,
                    sx.nombre AS sexo,
                    te.nombre AS tipo_estudiante,
                    c.nombre AS carrera,
                    se.nombre AS semestre,
                    se.numero AS semestre_numero,
                    ec.nombre AS estado_civil,

                    cl.nombre AS condicion_laboral,
                    rl.nombre AS relacion_laboral,
                    to2.nombre AS tipo_organizacion,
                    st.nombre AS sector_trabajo,
                    co.nombre AS categoria_ocupacional,

                    tc.nombre AS tipo_convivencia,
                    tv.nombre AS tipo_vivienda,
                    tv.valor_estrato AS tipo_vivienda_valor_estrato,
                    tnv.nombre AS tenencia_vivienda,

                    fa.nombre AS frecuencia_servicio_agua,
                    fas.nombre AS frecuencia_servicio_aseo,
                    fe.nombre AS frecuencia_servicio_electricidad,
                    fg.nombre AS frecuencia_servicio_gas,

                    tr.nombre AS transporte,
                    de.nombre AS dependencia_economica,
                    fif.nombre AS fuente_ingreso_familiar,
                    fif.valor_estrato AS fuente_ingreso_familiar_valor_estrato,
                    inf.nombre AS ingreso_familiar,

                    nep.nombre AS nivel_eduacion_padre,
                    nep.valor_estrato AS nivel_eduacion_padre_valor_estrato,
                    tep.nombre AS tipo_empresa_padre,
                    cop.nombre AS categoria_ocupacional_padre,
                    stp.nombre AS sector_trabajo_padre,

                    nem.nombre AS nivel_eduacion_madre,
                    nem.valor_estrato AS nivel_eduacion_madre_valor_estrato,
                    tem.nombre AS tipo_empresa_madre,
                    com.nombre AS categoria_ocupacional_madre,
                    stm.nombre AS sector_trabajo_madre,

                    v.nombre AS veracidad,
                    tb.nombre AS tipo_beca,

                    CASE
                        WHEN $puntajeCompletoSql THEN $puntajeSql
                        ELSE NULL
                    END AS estrato_puntaje,

                    CASE
                        WHEN $puntajeCompletoSql THEN
                            CASE
                                WHEN $puntajeSql <= 6 THEN 1
                                WHEN $puntajeSql <= 9 THEN 2
                                WHEN $puntajeSql <= 12 THEN 3
                                WHEN $puntajeSql <= 16 THEN 4
                                ELSE 5
                            END
                        ELSE NULL
                    END AS estrato

                FROM Encuesta e
                LEFT JOIN Instituto i ON i.id = e.instituto_id
                LEFT JOIN Nacionalidad n ON n.id = e.nacionalidad_id
                LEFT JOIN Sexo sx ON sx.id = e.sexo_id
                LEFT JOIN TipoEstudiante te ON te.id = e.tipo_estudiante_id
                LEFT JOIN Carrera c ON c.id = e.carrera_id
                LEFT JOIN Semestre se ON se.id = e.semestre_id
                LEFT JOIN EstadoCivil ec ON ec.id = e.estado_civil_id

                LEFT JOIN CondicionLaboral cl ON cl.id = e.condicion_laboral_id
                LEFT JOIN RelacionLaboral rl ON rl.id = e.trabajo_relacion_id
                LEFT JOIN TipoOrganizacion to2 ON to2.id = e.tipo_organizacion_id
                LEFT JOIN SectorTrabajo st ON st.id = e.sector_trabajo_id
                LEFT JOIN CategoriaOcupacional co ON co.id = e.categoria_ocupacional_id

                LEFT JOIN TipoConvivencia tc ON tc.id = e.tipo_convivencia_id
                LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
                LEFT JOIN TenenciaVivienda tnv ON tnv.id = e.tenencia_vivienda_id

                LEFT JOIN FrecuenciaServicioAgua fa ON fa.id = e.frecuencia_servicio_agua_id
                LEFT JOIN FrecuenciaServicioAseo fas ON fas.id = e.frecuencia_servicio_aseo_id
                LEFT JOIN FrecuenciaServicioElectricidad fe ON fe.id = e.frecuencia_servicio_electricidad_id
                LEFT JOIN FrecuenciaServicioGas fg ON fg.id = e.frecuencia_servicio_gas_id

                LEFT JOIN Transporte tr ON tr.id = e.transporte_id
                LEFT JOIN DependenciaEconomica de ON de.id = e.dependencia_economica_id
                LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
                LEFT JOIN IngresoFamiliar inf ON inf.id = e.ingreso_familiar_id

                LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
                LEFT JOIN TipoEmpresa tep ON tep.id = e.tipo_empresa_padre_id
                LEFT JOIN CategoriaOcupacional cop ON cop.id = e.categoria_ocupacional_padre_id
                LEFT JOIN SectorTrabajo stp ON stp.id = e.sector_trabajo_padre_id

                LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
                LEFT JOIN TipoEmpresa tem ON tem.id = e.tipo_empresa_madre_id
                LEFT JOIN CategoriaOcupacional com ON com.id = e.categoria_ocupacional_madre_id
                LEFT JOIN SectorTrabajo stm ON stm.id = e.sector_trabajo_madre_id

                LEFT JOIN Veracidad v ON v.id = e.veracidad_id
                LEFT JOIN TipoBeca tb ON tb.id = e.tipo_beca_id

                WHERE e.id = :id AND e.activo = 1";

        $bindings = ['id' => (int)$id];

        if (!empty($institutoId)) {
            $sql .= " AND e.instituto_id = :instituto_id";
            $bindings['instituto_id'] = (int)$institutoId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $encuesta = $stmt->fetch();

        if (!$encuesta) {
            return null;
        }

        $encuestaId = (int)($encuesta['id'] ?? $id);
        $encuesta['activos_vivienda'] = $this->getActivosVivienda($encuestaId);
        $encuesta['ambientes_vivienda'] = $this->getAmbientesVivienda($encuestaId);
        $encuesta['servicios_vivienda'] = $this->getServiciosVivienda($encuestaId);

        return $encuesta;
    }

    public function actualizarCompleta($id, array $datos, array $relaciones = [])
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $existsStmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE id = :id AND activo = 1 LIMIT 1");
            $existsStmt->execute(['id' => $id]);
            $exists = $existsStmt->fetch();

            if (!$exists) {
                $this->db->rollBack();
                return false;
            }

            if (!empty($datos)) {
                $setParts = [];
                $bindings = ['id' => $id];

                foreach ($datos as $column => $value) {
                    $setParts[] = $column . ' = :' . $column;
                    $bindings[$column] = $value;
                }

                if (!empty($setParts)) {
                    $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id AND activo = 1";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($bindings);
                }
            }

            if (array_key_exists('activos', $relaciones)) {
                $this->syncRelacionIds(
                    $id,
                    'ConjuntoActivoVivienda',
                    'encuesta_id',
                    'activo_vivienda_id',
                    $relaciones['activos']
                );
            }

            if (array_key_exists('servicios', $relaciones)) {
                $this->syncRelacionIds(
                    $id,
                    'ConjuntoServicioVivienda',
                    'encuesta_id',
                    'servicio_vivienda_id',
                    $relaciones['servicios']
                );
            }

            if (array_key_exists('ambientes', $relaciones)) {
                $this->syncRelacionIds(
                    $id,
                    'ConjuntoAmbienteVivienda',
                    'encuesta_id',
                    'ambiente_vivienda_id',
                    $relaciones['ambientes']
                );
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function syncRelacionIds($encuestaId, $table, $encuestaColumn, $catalogColumn, $ids)
    {
        $deleteSql = "DELETE FROM {$table} WHERE {$encuestaColumn} = :encuesta_id";
        $deleteStmt = $this->db->prepare($deleteSql);
        $deleteStmt->execute(['encuesta_id' => (int)$encuestaId]);

        if (!is_array($ids) || empty($ids)) {
            return;
        }

        $insertSql = "INSERT INTO {$table} ({$encuestaColumn}, {$catalogColumn}) VALUES (:encuesta_id, :catalog_id)";
        $insertStmt = $this->db->prepare($insertSql);

        $seen = [];
        foreach ($ids as $rawId) {
            if (!is_numeric($rawId)) {
                continue;
            }
            $catalogId = (int)$rawId;
            if ($catalogId <= 0 || isset($seen[$catalogId])) {
                continue;
            }
            $seen[$catalogId] = true;

            $insertStmt->execute([
                'encuesta_id' => (int)$encuestaId,
                'catalog_id' => $catalogId,
            ]);
        }
    }

    private function getActivosVivienda($encuestaId)
    {
        $stmt = $this->db->prepare("SELECT av.id, av.nombre
            FROM ConjuntoActivoVivienda cav
            INNER JOIN ActivoVivienda av ON av.id = cav.activo_vivienda_id
            WHERE cav.encuesta_id = :id
            ORDER BY av.nombre ASC");
        $stmt->execute(['id' => (int)$encuestaId]);
        return $stmt->fetchAll();
    }

    private function getAmbientesVivienda($encuestaId)
    {
        $stmt = $this->db->prepare("SELECT av.id, av.nombre
            FROM ConjuntoAmbienteVivienda cab
            INNER JOIN AmbienteVivienda av ON av.id = cab.ambiente_vivienda_id
            WHERE cab.encuesta_id = :id
            ORDER BY av.nombre ASC");
        $stmt->execute(['id' => (int)$encuestaId]);
        return $stmt->fetchAll();
    }

    private function getServiciosVivienda($encuestaId)
    {
        $stmt = $this->db->prepare("SELECT sv.id, sv.nombre
            FROM ConjuntoServicioVivienda csvv
            INNER JOIN ServicioVivienda sv ON sv.id = csvv.servicio_vivienda_id
            WHERE csvv.encuesta_id = :id
            ORDER BY sv.nombre ASC");
        $stmt->execute(['id' => (int)$encuestaId]);
        return $stmt->fetchAll();
    }
}