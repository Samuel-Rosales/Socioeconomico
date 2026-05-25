<?php
namespace App\Services;

use App\Core\Database;

class CatalogService {
    /**
     * Mapa de recursos a Modelos
     */
    private $modelMap = [
        // Tenant
        'instituto'               => 'App\Models\InstitutoModel',

        // Auth / Admin
        'rol'                     => 'App\Models\RolModel',

        // Identificación y Académico
        'nacionalidad'            => 'App\Models\NacionalidadModel',
        'sexo'                     => 'App\Models\SexoModel',
        'tipo-estudiante'          => 'App\Models\TipoEstudianteModel',
        'carrera'                  => 'App\Models\CarreraModel',
        'semestre'                 => 'App\Models\SemestreModel',

        // Situación Civil y Laboral
        'estado-civil'           => 'App\Models\EstadoCivilModel',
        'condicion-laboral'     => 'App\Models\CondicionLaboralModel',
        'relacion-laboral'      => 'App\Models\RelacionLaboralModel',
        'tipo-organizacion'        => 'App\Models\TipoOrganizacionModel',
        'sector-trabajo'          => 'App\Models\SectorTrabajoModel',
        'categoria-ocupacional'  => 'App\Models\CategoriaOcupacionalModel',

        // Vivienda y Convivencia
        'tipo-convivencia'         => 'App\Models\TipoConvivenciaModel',
        'tipo-vivienda'            => 'App\Models\TipoViviendaModel',
        'tenencia-vivienda'        => 'App\Models\TenenciaViviendaModel',
        'ambiente-vivienda'        => 'App\Models\AmbienteViviendaModel',
        'activo-vivienda'          => 'App\Models\ActivoViviendaModel',
        'servicio-vivienda'        => 'App\Models\ServicioViviendaModel',

        // Servicios y Frecuencias
        'frecuencia-agua'           => 'App\Models\FrecuenciaServicioAguaModel',
        'frecuencia-aseo'           => 'App\Models\FrecuenciaServicioAseoModel',
        'frecuencia-electricidad'   => 'App\Models\FrecuenciaServicioElectricidadModel',
        'frecuencia-gas'            => 'App\Models\FrecuenciaServicioGasModel',
        'transporte'               => 'App\Models\TransporteModel',

        // Economía y Educación
        'dependencia-economica'   => 'App\Models\DependenciaEconomicaModel',
        'fuente-ingreso'           => 'App\Models\FuenteIngresoFamiliarModel',
        'ingreso-familiar'       => 'App\Models\IngresoFamiliarModel',
        'nivel-educacion'         => 'App\Models\NivelEducacionModel',
        'tipo-empresa'             => 'App\Models\TipoEmpresaModel',

        // Otros
        'veracidad'                 => 'App\Models\VeracidadModel',
        'tipo-beca'                => 'App\Models\TipoBecaModel',
    ];

    /**
     * Catálogos que deben filtrarse por instituto (tenant).
     */
    private $tenantScopedResources = [
        'carrera',
        'tipo-beca',
    ];

    private $valorEstratoResources = [
        'tipo-vivienda',
        'fuente-ingreso',
        'nivel-educacion',
    ];

    // Etiquetas amigables para el menú de categorías (con acentos y estilo uniforme)
    private $resourceLabels = [
        'instituto' => 'Instituto',
        'rol' => 'Rol',
        'nacionalidad' => 'Nacionalidad',
        'sexo' => 'Sexo',
        'tipo-estudiante' => 'Tipo de estudiante',
        'carrera' => 'Carrera',
        'semestre' => 'Semestre',
        'estado-civil' => 'Estado civil',
        'condicion-laboral' => 'Condición laboral',
        'relacion-laboral' => 'Relación laboral',
        'tipo-organizacion' => 'Tipo de organización',
        'sector-trabajo' => 'Sector de trabajo',
        'categoria-ocupacional' => 'Categoría ocupacional',
        'tipo-convivencia' => 'Tipo de convivencia',
        'tipo-vivienda' => 'Tipo de vivienda',
        'tenencia-vivienda' => 'Tenencia de vivienda',
        'ambiente-vivienda' => 'Ambiente de vivienda',
        'activo-vivienda' => 'Activo de vivienda',
        'servicio-vivienda' => 'Servicio de vivienda',
        'frecuencia-agua' => 'Frecuencia de agua',
        'frecuencia-aseo' => 'Frecuencia de aseo',
        'frecuencia-electricidad' => 'Frecuencia de electricidad',
        'frecuencia-gas' => 'Frecuencia de gas',
        'transporte' => 'Transporte',
        'dependencia-economica' => 'Dependencia económica',
        'fuente-ingreso' => 'Fuente de ingreso',
        'ingreso-familiar' => 'Ingreso familiar',
        'nivel-educacion' => 'Nivel de educación',
        'tipo-empresa' => 'Tipo de empresa',
        'veracidad' => 'Veracidad',
        'tipo-beca' => 'Tipo de beca',
    ];

    /**
     * Mapa de carreras activas por sede.
     * Retorna: [carrera_id => [instituto_id, ...], ...]
     */
    public function getCarreraActivosPorInstituto()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT carrera_id, instituto_id FROM Instituto_Carrera WHERE activo = 1');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $map = [];
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $carreraId = isset($r['carrera_id']) ? (int)$r['carrera_id'] : 0;
            $institutoId = isset($r['instituto_id']) ? (int)$r['instituto_id'] : 0;
            if ($carreraId <= 0 || $institutoId <= 0) {
                continue;
            }
            if (!isset($map[$carreraId])) {
                $map[$carreraId] = [];
            }
            $map[$carreraId][] = $institutoId;
        }

        foreach ($map as $cid => $list) {
            $map[$cid] = array_values(array_unique(array_map('intval', $list)));
            sort($map[$cid]);
        }

        return $map;
    }

    public function getAdminCatalogItems($resource, $institutoId = null)
    {
        if (!is_string($resource) || $resource === '') {
            return null;
        }

        if (!isset($this->modelMap[$resource])) {
            return null;
        }

        // Especial: Carrera es por sede vía Instituto_Carrera
        if ($resource === 'carrera') {
            if (empty($institutoId)) {
                return [
                    'status' => 400,
                    'error' => "El catálogo '$resource' requiere instituto_id.",
                ];
            }

            $db = Database::getConnection();
            $sql = "SELECT c.id, c.nombre, COALESCE(ic.activo, 0) AS activo
                    FROM Carrera c
                    LEFT JOIN Instituto_Carrera ic
                      ON ic.carrera_id = c.id AND ic.instituto_id = :instituto_id
                    ORDER BY c.nombre ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['instituto_id' => (int)$institutoId]);
            return $stmt->fetchAll();
        }

        // Especial: TipoBeca depende de instituto_id por columna
        if ($resource === 'tipo-beca') {
            if (empty($institutoId)) {
                return [
                    'status' => 400,
                    'error' => "El catálogo '$resource' requiere instituto_id.",
                ];
            }

            $db = Database::getConnection();
            $sql = "SELECT id, instituto_id, nombre, activo
                    FROM TipoBeca
                    WHERE instituto_id = :instituto_id
                    ORDER BY nombre ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['instituto_id' => (int)$institutoId]);
            return $stmt->fetchAll();
        }

        $table = $this->resolveTableName($resource);
        if ($table === null) {
            return null;
        }

        $db = Database::getConnection();

        // Orden básico: por nombre si existe, si no por id.
        $orderBy = 'id ASC';
        if ($resource === 'semestre') {
            $orderBy = 'numero ASC';
        } elseif ($resource === 'instituto') {
            $orderBy = 'nombre ASC';
        } elseif ($resource === 'rol') {
            $orderBy = 'id ASC';
        } else {
            $orderBy = 'nombre ASC';
        }

        $sql = "SELECT * FROM `{$table}` ORDER BY {$orderBy}";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function adminCreate($resource, array $data, $institutoId = null)
    {
        if (!is_string($resource) || $resource === '') {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }

        if (!isset($this->modelMap[$resource])) {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }

        if ($resource === 'carrera') {
            return $this->adminCreateCarrera($data, $institutoId);
        }

        if ($resource === 'tipo-beca') {
            return $this->adminCreateTipoBeca($data, $institutoId);
        }

        $table = $this->resolveTableName($resource);
        if ($table === null) {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }

        $columns = $this->editableColumns($resource);
        $validated = $this->validateAndFilter($resource, $data, $columns, true);
        if (!$validated['success']) {
            return $validated;
        }

        $payload = $validated['data'];
        if (!array_key_exists('activo', $payload)) {
            $payload['activo'] = 1;
        }

        return $this->insertRow($table, $payload);
    }

    public function adminUpdate($resource, $id, array $data, $institutoId = null)
    {
        if (!is_string($resource) || $resource === '') {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }
        if (!isset($this->modelMap[$resource])) {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }
        if (!is_numeric($id) || (int)$id <= 0) {
            return ['success' => false, 'status' => 400, 'message' => 'ID inválido', 'errors' => ['id' => ['ID inválido.']]];
        }

        $id = (int)$id;

        if ($resource === 'carrera') {
            return $this->adminUpdateCarrera($id, $data, $institutoId);
        }

        if ($resource === 'tipo-beca') {
            return $this->adminUpdateTipoBeca($id, $data, $institutoId);
        }

        $table = $this->resolveTableName($resource);
        if ($table === null) {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }

        $columns = $this->editableColumns($resource);
        $validated = $this->validateAndFilter($resource, $data, $columns, false);
        if (!$validated['success']) {
            return $validated;
        }

        $payload = $validated['data'];
        if (empty($payload)) {
            return ['success' => false, 'status' => 400, 'message' => 'Sin cambios', 'errors' => ['data' => ['No hay campos para actualizar.']]];
        }

        return $this->updateRow($table, $id, $payload);
    }

    public function adminDelete($resource, $id, $institutoId = null)
    {
        if (!is_string($resource) || $resource === '') {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }
        if (!isset($this->modelMap[$resource])) {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }
        if (!is_numeric($id) || (int)$id <= 0) {
            return ['success' => false, 'status' => 400, 'message' => 'ID inválido', 'errors' => ['id' => ['ID inválido.']]];
        }

        $id = (int)$id;

        if ($resource === 'carrera') {
            return $this->setCarreraActivo($id, $institutoId, 0);
        }

        if ($resource === 'tipo-beca') {
            return $this->setTipoBecaActivo($id, $institutoId, 0);
        }

        $table = $this->resolveTableName($resource);
        if ($table === null) {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }

        return $this->setActivo($table, $id, 0);
    }

    public function adminRestore($resource, $id, $institutoId = null)
    {
        if (!is_string($resource) || $resource === '') {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }
        if (!isset($this->modelMap[$resource])) {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }
        if (!is_numeric($id) || (int)$id <= 0) {
            return ['success' => false, 'status' => 400, 'message' => 'ID inválido', 'errors' => ['id' => ['ID inválido.']]];
        }

        $id = (int)$id;

        if ($resource === 'carrera') {
            return $this->setCarreraActivo($id, $institutoId, 1);
        }

        if ($resource === 'tipo-beca') {
            return $this->setTipoBecaActivo($id, $institutoId, 1);
        }

        $table = $this->resolveTableName($resource);
        if ($table === null) {
            return ['success' => false, 'status' => 404, 'message' => 'Catálogo no encontrado'];
        }

        return $this->setActivo($table, $id, 1);
    }

    private function adminCreateCarrera(array $data, $institutoId)
    {
        $errors = [];
        if (empty($institutoId)) {
            $errors['instituto_id'][] = 'instituto_id es obligatorio para Carrera.';
        }

        $nombre = isset($data['nombre']) ? trim((string)$data['nombre']) : '';
        if ($nombre === '') {
            $errors['nombre'][] = 'nombre es obligatorio.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'status' => 400, 'message' => 'Validación fallida', 'errors' => $errors];
        }

        $db = Database::getConnection();

        try {
            $stmt = $db->prepare('INSERT INTO Carrera (nombre, activo) VALUES (:nombre, 1)');
            $stmt->execute(['nombre' => $nombre]);
            $carreraId = (int)$db->lastInsertId();
        } catch (\Throwable $e) {
            // Si ya existe por UNIQUE, buscamos el ID.
            $stmt = $db->prepare('SELECT id FROM Carrera WHERE nombre = :nombre LIMIT 1');
            $stmt->execute(['nombre' => $nombre]);
            $carreraId = (int)$stmt->fetchColumn();
            if ($carreraId <= 0) {
                return ['success' => false, 'status' => 400, 'message' => 'No se pudo crear Carrera', 'errors' => ['database' => [$e->getMessage()]]];
            }
        }

        // Asociamos a instituto (reactivando si existía)
        $stmt = $db->prepare('INSERT INTO Instituto_Carrera (instituto_id, carrera_id, activo) VALUES (:instituto_id, :carrera_id, 1)
                              ON DUPLICATE KEY UPDATE activo = 1');
        $stmt->execute(['instituto_id' => (int)$institutoId, 'carrera_id' => $carreraId]);

        return ['success' => true, 'data' => ['id' => $carreraId]];
    }

    private function adminUpdateCarrera($carreraId, array $data, $institutoId)
    {
        $db = Database::getConnection();
        $payload = [];
        $nombre = isset($data['nombre']) ? trim((string)$data['nombre']) : '';
        if ($nombre !== '') {
            $payload['nombre'] = $nombre;
        }

        if (!empty($payload)) {
            try {
                $stmt = $db->prepare('UPDATE Carrera SET nombre = :nombre WHERE id = :id');
                $stmt->execute(['nombre' => $payload['nombre'], 'id' => (int)$carreraId]);
            } catch (\Throwable $e) {
                return ['success' => false, 'status' => 400, 'message' => 'No se pudo actualizar Carrera', 'errors' => ['database' => [$e->getMessage()]]];
            }
        }

        // Si envían activo, lo aplicamos a la relación por instituto.
        if (array_key_exists('activo', $data)) {
            $activo = (int)$data['activo'] === 1 ? 1 : 0;
            $res = $this->setCarreraActivo((int)$carreraId, $institutoId, $activo);
            if (!$res['success']) {
                return $res;
            }
        }

        return ['success' => true, 'data' => ['id' => (int)$carreraId]];
    }

    private function setCarreraActivo($carreraId, $institutoId, $activo)
    {
        if (empty($institutoId)) {
            return ['success' => false, 'status' => 400, 'message' => 'instituto_id es obligatorio', 'errors' => ['instituto_id' => ['instituto_id es obligatorio.']]];
        }

        $db = Database::getConnection();
        $activo = (int)$activo === 1 ? 1 : 0;

        $stmt = $db->prepare('INSERT INTO Instituto_Carrera (instituto_id, carrera_id, activo) VALUES (:instituto_id, :carrera_id, :activo)
                              ON DUPLICATE KEY UPDATE activo = VALUES(activo)');
        $stmt->execute([
            'instituto_id' => (int)$institutoId,
            'carrera_id' => (int)$carreraId,
            'activo' => $activo,
        ]);

        return ['success' => true, 'data' => ['id' => (int)$carreraId, 'activo' => $activo]];
    }

    private function adminCreateTipoBeca(array $data, $institutoId)
    {
        $errors = [];
        if (empty($institutoId)) {
            $errors['instituto_id'][] = 'instituto_id es obligatorio para Tipo Beca.';
        }

        $nombre = isset($data['nombre']) ? trim((string)$data['nombre']) : '';
        if ($nombre === '') {
            $errors['nombre'][] = 'nombre es obligatorio.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'status' => 400, 'message' => 'Validación fallida', 'errors' => $errors];
        }

        $activo = array_key_exists('activo', $data) ? ((int)$data['activo'] === 1 ? 1 : 0) : 1;

        $db = Database::getConnection();
        try {
            $stmt = $db->prepare('INSERT INTO TipoBeca (instituto_id, nombre, activo) VALUES (:instituto_id, :nombre, :activo)');
            $stmt->execute([
                'instituto_id' => (int)$institutoId,
                'nombre' => $nombre,
                'activo' => $activo,
            ]);
            return ['success' => true, 'data' => ['id' => (int)$db->lastInsertId()]];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 400, 'message' => 'No se pudo crear Tipo Beca', 'errors' => ['database' => [$e->getMessage()]]];
        }
    }

    private function adminUpdateTipoBeca($id, array $data, $institutoId)
    {
        if (empty($institutoId)) {
            return ['success' => false, 'status' => 400, 'message' => 'instituto_id es obligatorio', 'errors' => ['instituto_id' => ['instituto_id es obligatorio.']]];
        }

        $payload = [];
        if (isset($data['nombre'])) {
            $nombre = trim((string)$data['nombre']);
            if ($nombre !== '') {
                $payload['nombre'] = $nombre;
            }
        }
        if (array_key_exists('activo', $data)) {
            $payload['activo'] = (int)$data['activo'] === 1 ? 1 : 0;
        }

        if (empty($payload)) {
            return ['success' => false, 'status' => 400, 'message' => 'Sin cambios', 'errors' => ['data' => ['No hay campos para actualizar.']]];
        }

        $db = Database::getConnection();
        $sets = [];
        $bind = ['id' => (int)$id, 'instituto_id' => (int)$institutoId];
        foreach ($payload as $col => $val) {
            $sets[] = "`$col` = :$col";
            $bind[$col] = $val;
        }

        $sql = 'UPDATE TipoBeca SET ' . implode(', ', $sets) . ' WHERE id = :id AND instituto_id = :instituto_id';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($bind);
            return ['success' => true, 'data' => ['id' => (int)$id]];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 400, 'message' => 'No se pudo actualizar Tipo Beca', 'errors' => ['database' => [$e->getMessage()]]];
        }
    }

    private function setTipoBecaActivo($id, $institutoId, $activo)
    {
        if (empty($institutoId)) {
            return ['success' => false, 'status' => 400, 'message' => 'instituto_id es obligatorio', 'errors' => ['instituto_id' => ['instituto_id es obligatorio.']]];
        }

        $db = Database::getConnection();
        $activo = (int)$activo === 1 ? 1 : 0;
        $sql = 'UPDATE TipoBeca SET activo = :activo WHERE id = :id AND instituto_id = :instituto_id';
        $stmt = $db->prepare($sql);
        $stmt->execute(['activo' => $activo, 'id' => (int)$id, 'instituto_id' => (int)$institutoId]);
        return ['success' => true, 'data' => ['id' => (int)$id, 'activo' => $activo]];
    }

    private function resolveTableName($resource)
    {
        if (!isset($this->modelMap[$resource])) {
            return null;
        }

        $modelClass = $this->modelMap[$resource];
        if (!class_exists($modelClass)) {
            return null;
        }

        try {
            $model = new $modelClass();
            if (!method_exists($model, 'getTableName')) {
                return null;
            }
            $table = $model->getTableName();
            $table = is_string($table) ? trim($table) : '';
            if ($table === '') {
                return null;
            }
            return $this->safeIdent($table);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function editableColumns($resource)
    {
        if ($resource === 'instituto') {
            return ['siglas', 'nombre', 'activo'];
        }
        if ($resource === 'semestre') {
            return ['nombre', 'numero', 'activo'];
        }
        if ($resource === 'rol') {
            return ['nombre', 'codigo', 'activo'];
        }
        if (in_array($resource, $this->valorEstratoResources, true)) {
            return ['nombre', 'valor_estrato', 'activo'];
        }
        // tipo-beca/carrera son especiales
        return ['nombre', 'activo'];
    }

    private function validateAndFilter($resource, array $data, array $allowedColumns, $isCreate)
    {
        $errors = [];
        $out = [];

        foreach ($allowedColumns as $col) {
            if (!array_key_exists($col, $data)) {
                continue;
            }

            $val = $data[$col];
            if ($col === 'activo') {
                $out[$col] = (int)$val === 1 ? 1 : 0;
                continue;
            }

            if ($col === 'numero' || $col === 'valor_estrato') {
                if ($val === '' || $val === null) {
                    // permitimos vacío en update
                    if ($isCreate) {
                        $errors[$col][] = "$col es obligatorio.";
                    }
                    continue;
                }
                if (!is_numeric($val)) {
                    $errors[$col][] = "$col debe ser numérico.";
                    continue;
                }
                $out[$col] = (int)$val;
                continue;
            }

            $str = trim((string)$val);
            if ($isCreate && $str === '' && ($col === 'nombre' || $col === 'siglas' || $col === 'codigo')) {
                $errors[$col][] = "$col es obligatorio.";
                continue;
            }
            if ($str !== '') {
                $out[$col] = $str;
            }
        }

        // Reglas mínimas por recurso
        if ($isCreate) {
            if ($resource === 'instituto') {
                if (empty($out['siglas'])) {
                    $errors['siglas'][] = 'siglas es obligatorio.';
                }
                if (empty($out['nombre'])) {
                    $errors['nombre'][] = 'nombre es obligatorio.';
                }
            } elseif ($resource === 'semestre') {
                if (empty($out['nombre'])) {
                    $errors['nombre'][] = 'nombre es obligatorio.';
                }
                if (!isset($out['numero'])) {
                    $errors['numero'][] = 'numero es obligatorio.';
                }
            } else {
                // Default
                if ($resource !== 'rol' && !in_array($resource, ['tipo-vivienda','fuente-ingreso','nivel-educacion'], true)) {
                    // default: nombre obligatorio
                    if (in_array('nombre', $allowedColumns, true) && empty($out['nombre'])) {
                        $errors['nombre'][] = 'nombre es obligatorio.';
                    }
                }
                if ($resource === 'rol') {
                    if (empty($out['nombre'])) {
                        $errors['nombre'][] = 'nombre es obligatorio.';
                    }
                    if (empty($out['codigo'])) {
                        $errors['codigo'][] = 'codigo es obligatorio.';
                    }
                }
                if (in_array($resource, $this->valorEstratoResources, true) && empty($out['nombre'])) {
                    $errors['nombre'][] = 'nombre es obligatorio.';
                }
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'status' => 400, 'message' => 'Validación fallida', 'errors' => $errors];
        }

        return ['success' => true, 'data' => $out];
    }

    private function insertRow($table, array $payload)
    {
        $db = Database::getConnection();

        $cols = [];
        $placeholders = [];
        $bind = [];
        foreach ($payload as $col => $val) {
            $safeCol = $this->safeIdent($col);
            $cols[] = "`{$safeCol}`";
            $placeholders[] = ':' . $safeCol;
            $bind[$safeCol] = $val;
        }

        $sql = 'INSERT INTO `' . $table . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($bind);
            return ['success' => true, 'data' => ['id' => (int)$db->lastInsertId()]];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 400, 'message' => 'No se pudo crear el registro', 'errors' => ['database' => [$e->getMessage()]]];
        }
    }

    private function updateRow($table, $id, array $payload)
    {
        $db = Database::getConnection();

        $sets = [];
        $bind = ['id' => (int)$id];
        foreach ($payload as $col => $val) {
            $safeCol = $this->safeIdent($col);
            $sets[] = "`{$safeCol}` = :{$safeCol}";
            $bind[$safeCol] = $val;
        }

        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE id = :id';
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($bind);
            return ['success' => true, 'data' => ['id' => (int)$id]];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 400, 'message' => 'No se pudo actualizar el registro', 'errors' => ['database' => [$e->getMessage()]]];
        }
    }

    private function setActivo($table, $id, $activo)
    {
        $db = Database::getConnection();
        $activo = (int)$activo === 1 ? 1 : 0;
        try {
            $stmt = $db->prepare('UPDATE `' . $table . '` SET activo = :activo WHERE id = :id');
            $stmt->execute(['activo' => $activo, 'id' => (int)$id]);
            return ['success' => true, 'data' => ['id' => (int)$id, 'activo' => $activo]];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 400, 'message' => 'No se pudo actualizar estado', 'errors' => ['database' => [$e->getMessage()]]];
        }
    }

    private function safeIdent($name)
    {
        $name = (string)$name;
        if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new \InvalidArgumentException('Identificador inválido');
        }
        return $name;
    }

    /**
     * Lista los catálogos disponibles para construir menús en el frontend.
     *
     * Retorna items con:
     * - resource: slug (para /catalogo/:resource)
     * - label: nombre humano (derivado del nombre de tabla del Model)
     * - tenant_scoped: si requiere instituto_id
     */
    public function listCatalogs($institutoId = null)
    {
        $items = [];

        // 1) Instanciamos modelos y recolectamos tablas (para validar existencia en 1 query)
        $resourceToTable = [];
        foreach ($this->modelMap as $resource => $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }

            try {
                $model = new $modelClass();
                if (!method_exists($model, 'getTableName')) {
                    continue;
                }
                $table = $model->getTableName();
                if (!is_string($table) || trim($table) === '') {
                    continue;
                }
                $resourceToTable[$resource] = $table;
            } catch (\Throwable $e) {
                // Si el model falla por cualquier razón, no lo listamos.
                continue;
            }
        }

        $existingTables = $this->getExistingTables(array_values($resourceToTable));

        // 2) Construimos el listado filtrando catálogos con tablas inexistentes.
        foreach ($resourceToTable as $resource => $table) {
            $tableKey = strtolower((string)$table);
            if (!isset($existingTables[$tableKey])) {
                continue;
            }

            $items[] = [
                'resource' => $resource,
                'label' => $this->resolveCatalogLabel($resource, $table),
                'tenant_scoped' => in_array($resource, $this->tenantScopedResources, true),
            ];
        }

        // Orden estable por label
        usort($items, function ($a, $b) {
            return strcmp((string)$a['label'], (string)$b['label']);
        });

        return $items;
    }

    private function getExistingTables(array $tables)
    {
        $tables = array_values(array_unique(array_filter($tables, function ($t) {
            return is_string($t) && trim($t) !== '';
        })));

        // Case-insensitive: normalizamos a lower para comparar con information_schema.
        $tablesLower = array_map(function ($t) {
            return strtolower((string)$t);
        }, $tables);

        if (empty($tablesLower)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tablesLower), '?'));
        $sql = "SELECT LOWER(TABLE_NAME) AS table_name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) IN ($placeholders)";

        $db = Database::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($tablesLower);

        $rows = $stmt->fetchAll();
        $set = [];
        foreach ($rows as $row) {
            if (isset($row['table_name'])) {
                $set[(string)$row['table_name']] = true;
            }
        }

        return $set;
    }

    private function humanizeTableName($table)
    {
        $table = (string)$table;
        // Inserta espacios en CamelCase (TipoEstudiante => Tipo Estudiante)
        $withSpaces = preg_replace('/(?<!^)([A-Z])/', ' $1', $table);
        $withSpaces = str_replace('_', ' ', (string)$withSpaces);
        $withSpaces = trim(preg_replace('/\s+/', ' ', $withSpaces));
        return $withSpaces !== '' ? $withSpaces : $table;
    }

    private function resolveCatalogLabel($resource, $table)
    {
        $resource = (string)$resource;
        if (isset($this->resourceLabels[$resource]) && is_string($this->resourceLabels[$resource])) {
            return $this->resourceLabels[$resource];
        }

        return $this->humanizeTableName($table);
    }

    /**
     * Lógica para obtener los datos de cualquier catálogo
     */
    public function getCatalogData($resource, $institutoId = null) {
        if (!isset($this->modelMap[$resource])) {
            return null;
        }

        $modelClass = $this->modelMap[$resource];
        $model = new $modelClass();

        // Catálogos multi-tenant: requieren instituto_id
        if (in_array($resource, $this->tenantScopedResources, true)) {
            if (empty($institutoId)) {
                return [
                    'status' => 400,
                    'error' => "El catálogo '$resource' requiere instituto_id (header X-Instituto-Id o query ?instituto_id=).",
                ];
            }

            if (method_exists($model, 'getAllByInstituto')) {
                return $model->getAllByInstituto((int)$institutoId);
            }
        }

        return $model->getAll();
    }

    /**
     * Obtiene todos los catálogos para el formulario en una sola llamada
     */
    public function getAllCatalogs($institutoId = null) {
        $result = [];
        $catalogKeys = [
            'nacionalidad', 'sexo', 'tipo-estudiante', 'carrera', 'semestre',
            'estado-civil', 'condicion-laboral', 'relacion-laboral', 'tipo-organizacion',
            'sector-trabajo', 'categoria-ocupacional', 'tipo-convivencia', 'tipo-vivienda',
            'tenencia-vivienda', 'ambiente-vivienda', 'activo-vivienda', 'servicio-vivienda',
            'frecuencia-agua', 'frecuencia-aseo', 'frecuencia-electricidad', 'frecuencia-gas',
            'transporte', 'dependencia-economica', 'fuente-ingreso', 'ingreso-familiar',
            'nivel-educacion', 'tipo-empresa', 'veracidad', 'tipo-beca',
        ];

        foreach ($catalogKeys as $resource) {
            $data = $this->getCatalogData($resource, $institutoId);
            $key = str_replace('-', '_', $resource);
            if (is_array($data) && !isset($data['error'])) {
                $result[$key] = $data;
            } else {
                $result[$key] = [];
            }
        }

        return $result;
    }
}