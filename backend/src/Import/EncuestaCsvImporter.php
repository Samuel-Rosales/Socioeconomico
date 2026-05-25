<?php

namespace App\Import;

use App\Core\Database;
use DateInterval;
use DateTime;
use Exception;
use PDO;
use PDOException;
use SplFileObject;

class EncuestaCsvImporter
{
    private $db;

    /**
     * Mapeo de aliases usados por frontend/legacy hacia columnas reales.
     * Mantener en sync con EncuestaService::normalizarRequestData().
     */
    private $aliases = [
        // Laboral
        'relacion_laboral_id' => 'trabajo_relacion_id',

        // Frecuencias
        'frecuencia_agua_id' => 'frecuencia_servicio_agua_id',
        'frecuencia_aseo_id' => 'frecuencia_servicio_aseo_id',
        'frecuencia_electricidad_id' => 'frecuencia_servicio_electricidad_id',
        'frecuencia_gas_id' => 'frecuencia_servicio_gas_id',

        // Economía
        'fuente_ingreso_id' => 'fuente_ingreso_familiar_id',

        // Padre/Madre
        'nivel_educacion_padre_id' => 'nivel_eduacion_padre_id',
        'padre_trabaja' => 'trabaja_padre',
        'nivel_educacion_madre_id' => 'nivel_eduacion_madre_id',
        'madre_trabaja' => 'trabaja_madre',
    ];

    private $fkMeta = [
        'instituto_id' => ['table' => 'Instituto', 'nameColumns' => ['siglas', 'nombre']],
        'nacionalidad_id' => ['table' => 'Nacionalidad', 'nameColumns' => ['nombre']],
        'sexo_id' => ['table' => 'Sexo', 'nameColumns' => ['nombre']],
        'tipo_estudiante_id' => ['table' => 'TipoEstudiante', 'nameColumns' => ['nombre']],
        'carrera_id' => ['table' => 'Carrera', 'nameColumns' => ['nombre']],
        'semestre_id' => ['table' => 'Semestre', 'nameColumns' => ['nombre'], 'extraColumns' => ['numero']],
        'estado_civil_id' => ['table' => 'EstadoCivil', 'nameColumns' => ['nombre']],
        'condicion_laboral_id' => ['table' => 'CondicionLaboral', 'nameColumns' => ['nombre']],
        'trabajo_relacion_id' => ['table' => 'RelacionLaboral', 'nameColumns' => ['nombre']],
        'tipo_organizacion_id' => ['table' => 'TipoOrganizacion', 'nameColumns' => ['nombre']],
        'sector_trabajo_id' => ['table' => 'SectorTrabajo', 'nameColumns' => ['nombre']],
        'categoria_ocupacional_id' => ['table' => 'CategoriaOcupacional', 'nameColumns' => ['nombre']],
        'tipo_convivencia_id' => ['table' => 'TipoConvivencia', 'nameColumns' => ['nombre']],
        'tipo_vivienda_id' => ['table' => 'TipoVivienda', 'nameColumns' => ['nombre']],
        'tenencia_vivienda_id' => ['table' => 'TenenciaVivienda', 'nameColumns' => ['nombre']],
        'frecuencia_servicio_agua_id' => ['table' => 'FrecuenciaServicioAgua', 'nameColumns' => ['nombre']],
        'frecuencia_servicio_aseo_id' => ['table' => 'FrecuenciaServicioAseo', 'nameColumns' => ['nombre']],
        'frecuencia_servicio_electricidad_id' => ['table' => 'FrecuenciaServicioElectricidad', 'nameColumns' => ['nombre']],
        'frecuencia_servicio_gas_id' => ['table' => 'FrecuenciaServicioGas', 'nameColumns' => ['nombre']],
        'transporte_id' => ['table' => 'Transporte', 'nameColumns' => ['nombre']],
        'dependencia_economica_id' => ['table' => 'DependenciaEconomica', 'nameColumns' => ['nombre']],
        'fuente_ingreso_familiar_id' => ['table' => 'FuenteIngresoFamiliar', 'nameColumns' => ['nombre']],
        'ingreso_familiar_id' => ['table' => 'IngresoFamiliar', 'nameColumns' => ['nombre']],
        'nivel_eduacion_padre_id' => ['table' => 'NivelEducacion', 'nameColumns' => ['nombre']],
        'tipo_empresa_padre_id' => ['table' => 'TipoEmpresa', 'nameColumns' => ['nombre']],
        'categoria_ocupacional_padre_id' => ['table' => 'CategoriaOcupacional', 'nameColumns' => ['nombre']],
        'sector_trabajo_padre_id' => ['table' => 'SectorTrabajo', 'nameColumns' => ['nombre']],
        'nivel_eduacion_madre_id' => ['table' => 'NivelEducacion', 'nameColumns' => ['nombre']],
        'tipo_empresa_madre_id' => ['table' => 'TipoEmpresa', 'nameColumns' => ['nombre']],
        'categoria_ocupacional_madre_id' => ['table' => 'CategoriaOcupacional', 'nameColumns' => ['nombre']],
        'sector_trabajo_madre_id' => ['table' => 'SectorTrabajo', 'nameColumns' => ['nombre']],
        'veracidad_id' => ['table' => 'Veracidad', 'nameColumns' => ['nombre']],
        'tipo_beca_id' => ['table' => 'TipoBeca', 'nameColumns' => ['nombre'], 'tenantScoped' => true, 'extraColumns' => ['instituto_id']],
    ];

    private $relationMeta = [
        'activos' => ['table' => 'ActivoVivienda', 'joinTable' => 'ConjuntoActivoVivienda', 'joinFk' => 'activo_vivienda_id'],
        'servicios' => ['table' => 'ServicioVivienda', 'joinTable' => 'ConjuntoServicioVivienda', 'joinFk' => 'servicio_vivienda_id'],
        'ambientes' => ['table' => 'AmbienteVivienda', 'joinTable' => 'ConjuntoAmbienteVivienda', 'joinFk' => 'ambiente_vivienda_id'],
    ];

    /**
     * Aliases de valores (normalizados) para mejorar matching contra catálogos.
     * Se aplica cuando el CSV trae pequeñas variantes/typos (por ejemplo export de Forms).
     */
    private $fkValueAliases = [
        'instituto_id' => [
            'iujo barquisimeto' => 'iujo-barquisimeto',
            'iujo caracas' => 'iujo-caracas',
            'iujo petare' => 'iujo-petare',
            'iujo guanarito' => 'iujo-guanarito',
            'iusf' => 'iusf',
        ],

        // Frecuencias: en el seed existe "Esporádicamente"; en CSV suele venir "Exporadicamente".
        'frecuencia_servicio_agua_id' => [
            'exporadicamente' => 'esporadicamente / irregular',
            'esporadicamente' => 'esporadicamente / irregular',
            'irregular' => 'esporadicamente / irregular',
        ],
        'frecuencia_servicio_aseo_id' => [
            'exporadicamente' => 'esporadicamente',
            'irregular' => 'esporadicamente',
        ],

        // Tipo de vivienda: variante no contemplada en el seed original
        'tipo_vivienda_id' => [
            'casa-apto lujoso y espacioso' => 'quinta o apartamento de lujo',
        ],

        // Transporte: valor inválido detectado en un registro del CSV (parece un servicio, no un transporte).
        // Lo tratamos como no aplicable para no bloquear la importación.
        'transporte_id' => [
            'aseo / recoleccion de basura' => null,
        ],

        // Tipo de beca: variantes/expansiones de la opción "QG" y "No posee beca".
        'tipo_beca_id' => [
            'becado por la asociacion queremos graduarnos (qg)' => 'qg',
            'por qg' => 'qg',
            'qg queremos graduarnos' => 'qg',
            'no tengo ninguna beca' => 'no posee beca',
        ],
    ];

    public function __construct(PDO $db = null)
    {
        $this->db = $db ?: Database::getConnection();
    }

    /**
     * @param string $filePath
     * @param array $options
     * @return array Reporte de importación
     */
    public function import($filePath, array $options = [])
    {
        $defaults = [
            'delimiter' => ',',
            'enclosure' => '"',
            'escape' => '\\',
            'encoding' => 'UTF-8',
            'dry_run' => false,
            'stop_on_error' => false,
            'strict_fks' => true,
            'strict_validation' => true,
            'default_instituto_id' => null,
            'on_duplicate' => 'skip', // skip|error
            'batch_size' => 200,
            'max_error_rows' => 500,
            'map' => null, // array(headerName => targetKey)
        ];

        $opts = array_merge($defaults, $options);

        if (!is_string($filePath) || trim($filePath) === '' || !file_exists($filePath)) {
            return [
                'success' => false,
                'message' => 'Archivo no encontrado: ' . $filePath,
                'summary' => [],
                'errors' => [],
            ];
        }

        $schema = $this->describeTable('Encuesta');
        $columns = array_keys($schema);

        $catalogCache = $this->buildCatalogCache();

        $file = new SplFileObject($filePath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($opts['delimiter'], $opts['enclosure'], $opts['escape']);

        $rawHeader = $file->fgetcsv();
        if ($rawHeader === false || $rawHeader === null) {
            return [
                'success' => false,
                'message' => 'El CSV está vacío o no tiene header.',
                'summary' => [],
                'errors' => [],
            ];
        }

        $header = [];
        foreach ($rawHeader as $h) {
            $header[] = $this->normalizeHeader($h);
        }

        $map = $this->loadMapOption($opts['map']);

        $columnTargets = [];
        foreach ($header as $idx => $headerName) {
            $target = null;

            if (isset($map[$headerName])) {
                $target = $map[$headerName];
            } else {
                $target = $this->inferTargetKey($headerName, $columns);
            }

            if ($target === null || $target === '' || $target === '__ignore__') {
                continue;
            }

            $columnTargets[$idx] = $target;
        }

        // Validación de columnas mínimas
        $required = $opts['strict_validation']
            ? [
                'email',
                'nombres',
                'apellidos',
                'cedula',
                'telefono',
                'fecha_nacimiento',
                'direccion',
                'hijos',
                'estudio_fya',
                'instituto_id',
                'nacionalidad_id',
                'sexo_id',
                'estado_civil_id',
                'tipo_estudiante_id',
                'carrera_id',
                'semestre_id',
                'veracidad_id',
            ]
            : [
                'email',
                'nombres',
                'apellidos',
                'cedula',
                'instituto_id',
            ];

        $hasDefaultInstituto = isset($opts['default_instituto_id'])
            && $opts['default_instituto_id'] !== null
            && (int)$opts['default_instituto_id'] > 0;

        $missingRequired = [];
        foreach ($required as $req) {
            if ($req === 'instituto_id' && $hasDefaultInstituto) {
                // Si no viene instituto_id en el CSV, lo podemos completar con --default-instituto-id.
                continue;
            }

            if (($req === 'nombres' || $req === 'apellidos') && $this->isTargetPresent($columnTargets, 'nombre_completo')) {
                // Permitimos derivar nombres/apellidos a partir de un campo único (ej. Google Forms: "Apellidos y Nombres").
                continue;
            }

            if (!$this->isTargetPresent($columnTargets, $req)) {
                $missingRequired[] = $req;
            }
        }
        if (!empty($missingRequired)) {
            return [
                'success' => false,
                'message' => 'El CSV no incluye (o no mapea) columnas requeridas: ' . implode(', ', $missingRequired),
                'summary' => [],
                'errors' => [],
            ];
        }

        // Preparamos inserts
        $encuestaColumns = array_values(array_filter($columns, function ($c) {
            return $c !== 'id';
        }));

        $insertSql = 'INSERT INTO Encuesta (' . implode(', ', $encuestaColumns) . ') VALUES (:' . implode(', :', $encuestaColumns) . ')';
        $stmtInsertEncuesta = $this->db->prepare($insertSql);

        $stmtInsertActivo = $this->db->prepare('INSERT INTO ConjuntoActivoVivienda (encuesta_id, activo_vivienda_id) VALUES (?, ?)');
        $stmtInsertServicio = $this->db->prepare('INSERT INTO ConjuntoServicioVivienda (encuesta_id, servicio_vivienda_id) VALUES (?, ?)');
        $stmtInsertAmbiente = $this->db->prepare('INSERT INTO ConjuntoAmbienteVivienda (encuesta_id, ambiente_vivienda_id) VALUES (?, ?)');

        $counters = [
            'rows_total' => 0,
            'rows_ok' => 0,
            'rows_failed' => 0,
            'rows_skipped_duplicates' => 0,
        ];

        $errorRows = [];
        $extraErrorCount = 0;

        $inTransaction = false;
        $batchSize = max(1, (int)$opts['batch_size']);
        $batchCount = 0;

        // CSV: 1 header + N data. Arrancamos en línea 2.
        $rowNumber = 1;

        while (!$file->eof()) {
            $row = $file->fgetcsv();
            $rowNumber++;

            if ($row === false || $row === null) {
                continue;
            }

            // Línea vacía
            if (count($row) === 1 && ($row[0] === null || trim((string)$row[0]) === '')) {
                continue;
            }

            $counters['rows_total']++;

            $rawData = $this->mapRow($row, $columnTargets, $opts['encoding']);

            // Defaults / aliases
            $rawData = $this->applyAliases($rawData);

            // Campos derivados (ej. separar "nombre_completo" en "apellidos" y "nombres")
            $rawData = $this->applyDerivedFields($rawData);

            if (!isset($rawData['instituto_id']) || $rawData['instituto_id'] === null || $rawData['instituto_id'] === '') {
                if (!empty($opts['default_instituto_id'])) {
                    $rawData['instituto_id'] = $opts['default_instituto_id'];
                }
            }

            // Separar relaciones
            $relations = $this->extractRelations($rawData);

            // Track de columnas explícitamente provistas por el CSV (para no sobre-escribir NULL con defaults)
            $providedKeys = array_fill_keys(array_keys($rawData), true);

            // Convertir/validar
            $rowErrors = [];
            $data = [];

            // Resolver instituto primero (necesario para TipoBeca)
            $institutoId = null;
            if (array_key_exists('instituto_id', $rawData)) {
                $institutoId = $this->resolveForeignKey('instituto_id', $rawData['instituto_id'], $catalogCache, $opts, null, $rowErrors);
                $data['instituto_id'] = $institutoId;
            }

            foreach ($encuestaColumns as $col) {
                if ($col === 'instituto_id') {
                    continue;
                }

                $rawValue = array_key_exists($col, $rawData) ? $rawData[$col] : null;

                $converted = $this->convertValueForColumn($col, $rawValue, $schema, $catalogCache, $opts, $institutoId, $rowErrors);
                $data[$col] = $converted;
            }

            // Completar defaults para columnas no presentes
            foreach ($encuestaColumns as $col) {
                if (!array_key_exists($col, $data)) {
                    $data[$col] = null;
                }

                if ($data[$col] === null && !isset($providedKeys[$col])) {
                    $default = $this->defaultForColumn($col, $schema);
                    if ($default !== null) {
                        $data[$col] = $default;
                    }
                }
            }

            // Validaciones finales (strict)
            $this->validateRow($data, $opts, $rowErrors);

            // Convertir relaciones (IDs)
            $relResolved = $this->resolveRelations($relations, $catalogCache, $opts, $rowErrors);

            if (!empty($rowErrors)) {
                $counters['rows_failed']++;

                if (count($errorRows) < (int)$opts['max_error_rows']) {
                    $errorRows[] = [
                        'row' => $rowNumber,
                        'cedula' => isset($data['cedula']) ? $data['cedula'] : null,
                        'errors' => $rowErrors,
                    ];
                } else {
                    $extraErrorCount++;
                }

                if (!empty($opts['stop_on_error'])) {
                    break;
                }

                continue;
            }

            if (!empty($opts['dry_run'])) {
                $counters['rows_ok']++;
                continue;
            }

            // Transacciones por lotes + savepoints por fila
            $savepoint = null;
            try {
                if (!$inTransaction) {
                    $this->db->beginTransaction();
                    $inTransaction = true;
                    $batchCount = 0;
                }

                $batchCount++;

                $savepoint = 'r' . $rowNumber;
                $this->db->exec('SAVEPOINT ' . $savepoint);

                $stmtInsertEncuesta->execute($data);
                $encuestaId = $this->db->lastInsertId();

                // Relaciones M:N
                if (!empty($relResolved['activos'])) {
                    foreach ($relResolved['activos'] as $id) {
                        $stmtInsertActivo->execute([(int)$encuestaId, (int)$id]);
                    }
                }
                if (!empty($relResolved['servicios'])) {
                    foreach ($relResolved['servicios'] as $id) {
                        $stmtInsertServicio->execute([(int)$encuestaId, (int)$id]);
                    }
                }
                if (!empty($relResolved['ambientes'])) {
                    foreach ($relResolved['ambientes'] as $id) {
                        $stmtInsertAmbiente->execute([(int)$encuestaId, (int)$id]);
                    }
                }

                $this->db->exec('RELEASE SAVEPOINT ' . $savepoint);

                $counters['rows_ok']++;

                if ($batchCount >= $batchSize) {
                    $this->db->commit();
                    $inTransaction = false;
                }
            } catch (PDOException $e) {
                if ($inTransaction && $savepoint !== null) {
                    try {
                        $this->db->exec('ROLLBACK TO SAVEPOINT ' . $savepoint);
                    } catch (Exception $ignored) {
                        // Si falla el savepoint, hacemos rollback total del batch.
                        $this->db->rollBack();
                        $inTransaction = false;
                    }
                }

                if ($this->isDuplicateCedulaError($e)) {
                    if ($opts['on_duplicate'] === 'skip') {
                        $counters['rows_skipped_duplicates']++;
                        continue;
                    }
                }

                $counters['rows_failed']++;

                if (count($errorRows) < (int)$opts['max_error_rows']) {
                    $errorRows[] = [
                        'row' => $rowNumber,
                        'cedula' => isset($data['cedula']) ? $data['cedula'] : null,
                        'errors' => [
                            'database' => [
                                $e->getMessage(),
                            ],
                        ],
                    ];
                } else {
                    $extraErrorCount++;
                }

                if (!empty($opts['stop_on_error'])) {
                    break;
                }
            }
        }

        if ($inTransaction) {
            try {
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Fallo al hacer commit final: ' . $e->getMessage(),
                    'summary' => $counters,
                    'errors' => $errorRows,
                    'extra_error_count' => $extraErrorCount,
                ];
            }
        }

        $success = ($counters['rows_failed'] === 0);

        return [
            'success' => $success,
            'message' => $success ? 'Importación completada' : 'Importación completada con errores',
            'summary' => $counters,
            'errors' => $errorRows,
            'extra_error_count' => $extraErrorCount,
        ];
    }

    private function loadMapOption($mapOption)
    {
        if ($mapOption === null) {
            return [];
        }

        if (is_array($mapOption)) {
            // Si el usuario pasó directamente un array
            return $this->normalizeMapArray($mapOption);
        }

        if (!is_string($mapOption) || trim($mapOption) === '') {
            return [];
        }

        if (!file_exists($mapOption)) {
            throw new Exception('Archivo de mapa no encontrado: ' . $mapOption);
        }

        $json = file_get_contents($mapOption);
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido en mapa: ' . json_last_error_msg());
        }

        if (isset($decoded['columns']) && is_array($decoded['columns'])) {
            $decoded = $decoded['columns'];
        }

        if (!is_array($decoded)) {
            throw new Exception('El mapa debe ser un objeto JSON con pares header->campo');
        }

        return $this->normalizeMapArray($decoded);
    }

    private function normalizeMapArray(array $map)
    {
        $out = [];
        foreach ($map as $k => $v) {
            $key = $this->normalizeHeader($k);
            $out[$key] = is_string($v) ? trim($v) : $v;
        }
        return $out;
    }

    private function normalizeHeader($value)
    {
        $v = is_string($value) ? trim($value) : '';
        if (substr($v, 0, 3) === "\xEF\xBB\xBF") {
            $v = substr($v, 3);
        }
        return $v;
    }

    private function inferTargetKey($headerName, array $schemaColumns)
    {
        if (!is_string($headerName) || trim($headerName) === '') {
            return null;
        }

        $headerName = trim($headerName);

        // Relaciones (si vienen como columnas directas)
        $rel = $this->inferRelationKey($headerName);
        if ($rel !== null) {
            return 'rel:' . $rel;
        }

        // Alias exacto
        if (isset($this->aliases[$headerName])) {
            $candidate = $this->aliases[$headerName];
            if (in_array($candidate, $schemaColumns, true)) {
                return $candidate;
            }
        }

        // Match exacto
        if (in_array($headerName, $schemaColumns, true)) {
            return $headerName;
        }

        // Casos especiales: instituto_siglas / instituto_nombre
        if ($headerName === 'instituto_siglas' || $headerName === 'instituto_nombre' || $headerName === 'instituto') {
            if (in_array('instituto_id', $schemaColumns, true)) {
                return 'instituto_id';
            }
        }

        // *_nombre / *_siglas / *_numero
        $suffixes = ['_nombre', '_siglas', '_numero'];
        foreach ($suffixes as $suffix) {
            if ($this->endsWith($headerName, $suffix)) {
                $base = substr($headerName, 0, -strlen($suffix));
                $candidate = $base . '_id';
                if (isset($this->aliases[$candidate])) {
                    $candidate = $this->aliases[$candidate];
                }
                if (in_array($candidate, $schemaColumns, true)) {
                    return $candidate;
                }
            }
        }

        // Si no termina en _id, probamos base_id
        if (!$this->endsWith($headerName, '_id')) {
            $candidate = $headerName . '_id';
            if (isset($this->aliases[$candidate])) {
                $candidate = $this->aliases[$candidate];
            }
            if (in_array($candidate, $schemaColumns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function inferRelationKey($headerName)
    {
        $h = strtolower($headerName);
        $map = [
            'activos' => 'activos',
            'activos_vivienda' => 'activos',
            'servicios' => 'servicios',
            'servicios_vivienda' => 'servicios',
            'ambientes' => 'ambientes',
            'ambientes_vivienda' => 'ambientes',
        ];

        return isset($map[$h]) ? $map[$h] : null;
    }

    private function endsWith($haystack, $needle)
    {
        $len = strlen($needle);
        if ($len === 0) {
            return true;
        }
        return substr($haystack, -$len) === $needle;
    }

    private function isTargetPresent(array $columnTargets, $target)
    {
        foreach ($columnTargets as $t) {
            if ($t === $target) {
                return true;
            }
        }
        return false;
    }

    private function mapRow(array $row, array $columnTargets, $encoding)
    {
        $out = [];
        foreach ($columnTargets as $idx => $target) {
            if (!array_key_exists($idx, $row)) {
                continue;
            }

            $val = $this->cleanValue($row[$idx], $encoding);

            // Relaciones
            if (is_string($target) && strpos($target, 'rel:') === 0) {
                $relKey = substr($target, 4);
                $out['rel:' . $relKey] = $val;
                continue;
            }

            $out[$target] = $val;
        }

        return $out;
    }

    private function cleanValue($value, $encoding)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $v = trim($value);
            $v = str_replace("\xC2\xA0", ' ', $v);
            $v = trim($v);

            if ($v === '') {
                return null;
            }

            if (is_string($encoding) && strtoupper($encoding) !== 'UTF-8') {
                $converted = @iconv($encoding, 'UTF-8//TRANSLIT', $v);
                if ($converted !== false && $converted !== null) {
                    $v = $converted;
                }
            }

            return $v;
        }

        if (is_numeric($value)) {
            return (string)$value;
        }

        return $value;
    }

    private function applyAliases(array $data)
    {
        foreach ($this->aliases as $from => $to) {
            if (array_key_exists($from, $data) && !array_key_exists($to, $data)) {
                $data[$to] = $data[$from];
            }
        }

        // Convertir strings vacíos a NULL para claves *_id
        foreach ($data as $key => $value) {
            if ($this->endsWith($key, '_id') && is_string($value) && trim($value) === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    private function applyDerivedFields(array $data)
    {
        // Derivar nombres/apellidos desde un campo único
        if ((empty($data['nombres']) || empty($data['apellidos'])) && !empty($data['nombre_completo'])) {
            $parts = $this->splitNombreCompleto($data['nombre_completo']);

            if (empty($data['apellidos']) && !empty($parts['apellidos'])) {
                $data['apellidos'] = $parts['apellidos'];
            }
            if (empty($data['nombres']) && !empty($parts['nombres'])) {
                $data['nombres'] = $parts['nombres'];
            }
        }

        return $data;
    }

    private function splitNombreCompleto($value)
    {
        $v = is_string($value) ? trim($value) : '';
        $v = preg_replace('/\s+/', ' ', $v);
        $v = trim((string)$v);

        if ($v === '') {
            return ['apellidos' => '', 'nombres' => ''];
        }

        // Formato "Apellidos, Nombres"
        if (strpos($v, ',') !== false) {
            $parts = explode(',', $v, 2);
            $apellidos = trim($parts[0]);
            $nombres = trim(isset($parts[1]) ? $parts[1] : '');
            if ($apellidos === '' && $nombres !== '') {
                $apellidos = $nombres;
            }
            if ($nombres === '' && $apellidos !== '') {
                $nombres = $apellidos;
            }
            return ['apellidos' => $apellidos, 'nombres' => $nombres];
        }

        $tokens = preg_split('/\s+/', $v);
        $tokens = array_values(array_filter($tokens, function ($t) {
            return $t !== null && trim((string)$t) !== '';
        }));

        $count = count($tokens);
        if ($count === 1) {
            // Caso extremo: ponemos el mismo valor en ambos para no bloquear import.
            return ['apellidos' => $tokens[0], 'nombres' => $tokens[0]];
        }

        if ($count === 2) {
            return ['apellidos' => $tokens[0], 'nombres' => $tokens[1]];
        }

        // Heurística común (Venezuela): 2 apellidos + resto nombres.
        $apellidos = $tokens[0] . ' ' . $tokens[1];
        $nombres = implode(' ', array_slice($tokens, 2));
        return ['apellidos' => trim($apellidos), 'nombres' => trim($nombres)];
    }

    private function extractRelations(array &$rawData)
    {
        $rels = [
            'activos' => null,
            'servicios' => null,
            'ambientes' => null,
        ];

        foreach (array_keys($rels) as $k) {
            $key = 'rel:' . $k;
            if (array_key_exists($key, $rawData)) {
                $rels[$k] = $rawData[$key];
                unset($rawData[$key]);
            }
        }

        return $rels;
    }

    private function describeTable($table)
    {
        $stmt = $this->db->prepare('DESCRIBE ' . $table);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $schema = [];
        foreach ($rows as $r) {
            $field = $r['Field'];
            $schema[$field] = [
                'type' => $r['Type'],
                'nullable' => ($r['Null'] === 'YES'),
                'default' => $r['Default'],
            ];
        }

        return $schema;
    }

    private function buildCatalogCache()
    {
        $cache = [
            'byTable' => [],
            'fk' => [],
            'tipoBecaById' => [],
        ];

        // Pre-cargar catálogos para resolución por nombre y validación de IDs.
        $tables = [
            'Instituto' => ['siglas', 'nombre'],
            'Nacionalidad' => ['nombre'],
            'Sexo' => ['nombre'],
            'TipoEstudiante' => ['nombre'],
            'Carrera' => ['nombre'],
            'Semestre' => ['nombre', 'numero'],
            'EstadoCivil' => ['nombre'],
            'CondicionLaboral' => ['nombre'],
            'RelacionLaboral' => ['nombre'],
            'TipoOrganizacion' => ['nombre'],
            'SectorTrabajo' => ['nombre'],
            'CategoriaOcupacional' => ['nombre'],
            'TipoConvivencia' => ['nombre'],
            'TipoVivienda' => ['nombre'],
            'TenenciaVivienda' => ['nombre'],
            'FrecuenciaServicioAgua' => ['nombre'],
            'FrecuenciaServicioAseo' => ['nombre'],
            'FrecuenciaServicioElectricidad' => ['nombre'],
            'FrecuenciaServicioGas' => ['nombre'],
            'Transporte' => ['nombre'],
            'DependenciaEconomica' => ['nombre'],
            'FuenteIngresoFamiliar' => ['nombre'],
            'IngresoFamiliar' => ['nombre'],
            'NivelEducacion' => ['nombre'],
            'TipoEmpresa' => ['nombre'],
            'Veracidad' => ['nombre'],
            'TipoBeca' => ['nombre', 'instituto_id'],
            // Relaciones
            'ActivoVivienda' => ['nombre'],
            'ServicioVivienda' => ['nombre'],
            'AmbienteVivienda' => ['nombre'],
        ];

        foreach ($tables as $table => $cols) {
            $cache['byTable'][$table] = $this->loadCatalogTable($table, $cols);
        }

        // Índices por FK-column
        foreach ($this->fkMeta as $fkCol => $meta) {
            $table = $meta['table'];
            $cache['fk'][$fkCol] = [
                'ids' => isset($cache['byTable'][$table]['ids']) ? $cache['byTable'][$table]['ids'] : [],
                'byName' => isset($cache['byTable'][$table]['byName']) ? $cache['byTable'][$table]['byName'] : [],
                'extra' => isset($cache['byTable'][$table]['extra']) ? $cache['byTable'][$table]['extra'] : [],
            ];
        }

        // TipoBeca: cache por instituto y por id
        if (isset($cache['byTable']['TipoBeca'])) {
            $tipoBecaRows = $cache['byTable']['TipoBeca']['rows'];
            $byInstituto = [];
            $byIdInstituto = [];
            foreach ($tipoBecaRows as $r) {
                $id = (int)$r['id'];
                $inst = (int)$r['instituto_id'];
                $nameKey = $this->normalizeCatalogKey($r['nombre']);
                if (!isset($byInstituto[$inst])) {
                    $byInstituto[$inst] = [];
                }
                $byInstituto[$inst][$nameKey] = $id;
                $byIdInstituto[$id] = $inst;
            }
            $cache['fk']['tipo_beca_id']['byInstituto'] = $byInstituto;
            $cache['tipoBecaById'] = $byIdInstituto;
        }

        return $cache;
    }

    private function loadCatalogTable($table, array $cols)
    {
        $selectCols = array_merge(['id'], $cols);
        $sql = 'SELECT ' . implode(', ', $selectCols) . ' FROM ' . $table;

        // Convención: catálogos tienen "activo".
        // Evitamos fallar si alguna tabla no lo tiene.
        try {
            $sql .= ' WHERE activo = 1';
        } catch (Exception $ignored) {
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ids = [];
        $byName = [];
        $extra = [];

        foreach ($rows as $r) {
            $ids[(int)$r['id']] = true;

            if (isset($r['nombre'])) {
                $byName[$this->normalizeCatalogKey($r['nombre'])] = (int)$r['id'];
            }
            if (isset($r['siglas'])) {
                $byName[$this->normalizeCatalogKey($r['siglas'])] = (int)$r['id'];
            }
            if (isset($r['numero'])) {
                // Semestre: mapear por numero
                $extra['numero'][(int)$r['numero']] = (int)$r['id'];
            }
        }

        return [
            'rows' => $rows,
            'ids' => $ids,
            'byName' => $byName,
            'extra' => $extra,
        ];
    }

    private function normalizeCatalogKey($value)
    {
        $v = is_string($value) ? trim($value) : '';
        $v = preg_replace('/\s+/', ' ', $v);

        // Importante: NO usar strtolower() sobre UTF-8 antes de iconv(),
        // porque strtolower() es byte-wise y puede corromper secuencias multibyte.
        // Primero transliteramos a ASCII, luego pasamos a minúsculas.
        $noAccents = @iconv('UTF-8', 'ASCII//TRANSLIT', $v);
        if ($noAccents !== false && $noAccents !== null) {
            $v = $noAccents;
        }

        $v = strtolower($v);

        // iconv puede introducir apostrofes para marcar acentos (ej. "espor'adicamente").
        // Los removemos para mejorar matching entre valores con/sin acentos.
        $v = str_replace(["'", '`', '´', '’'], '', $v);

        // Re-normalizar espacios
        $v = preg_replace('/\s+/', ' ', $v);

        return trim($v);
    }

    private function isNotApplicableValue($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $v = trim($value);
        if ($v === '') {
            return false;
        }

        $k = $this->normalizeCatalogKey($v);

        return in_array($k, ['no aplica', 'no aplicable', 'n/a', 'na'], true);
    }

    private function defaultForColumn($column, array $schema)
    {
        if (!isset($schema[$column])) {
            return null;
        }

        $def = $schema[$column]['default'];
        if ($def === null) {
            return null;
        }

        $type = $schema[$column]['type'];

        // CURRENT_TIMESTAMP
        if (is_string($def) && stripos($def, 'current_timestamp') !== false) {
            return date('Y-m-d H:i:s');
        }

        // Cast simple para numéricos
        if ($this->isIntType($type)) {
            if (is_numeric($def)) {
                return (int)$def;
            }
        }

        if ($this->isTinyIntType($type)) {
            if (is_numeric($def)) {
                return (int)$def;
            }
        }

        return $def;
    }

    private function convertValueForColumn($column, $rawValue, array $schema, array $catalogCache, array $opts, $institutoId, array &$rowErrors)
    {
        // Si no vino, devolvemos null (se completa con default después)
        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        // Ignorar id si viene
        if ($column === 'id') {
            return null;
        }

        // Bool
        $boolCols = [
            'hijos',
            'estudio_fya',
            'trabaja_padre',
            'padre_en_venezuela',
            'padre_egresado_iujo',
            'trabaja_madre',
            'madre_en_venezuela',
            'madre_egresada_iujo',
            'activo',
        ];
        if (in_array($column, $boolCols, true)) {
            $b = $this->parseBoolean($rawValue);
            if ($b === null) {
                if ($this->isNotApplicableValue($rawValue)) {
                    return null;
                }

                $rowErrors[$column][] = 'Valor booleano inválido: ' . $rawValue;
                return null;
            }
            return $b;
        }

        // Fechas
        if ($column === 'fecha_nacimiento') {
            $d = $this->parseDate($rawValue);
            if ($d === null) {
                $rowErrors[$column][] = 'Fecha inválida (esperado YYYY-MM-DD o DD/MM/YYYY): ' . $rawValue;
                return null;
            }
            return $d;
        }

        if ($column === 'creado') {
            $dt = $this->parseDateTime($rawValue);
            if ($dt === null) {
                $rowErrors[$column][] = 'Datetime inválido: ' . $rawValue;
                return null;
            }
            return $dt;
        }

        // FK
        if ($this->endsWith($column, '_id') && isset($this->fkMeta[$column])) {
            return $this->resolveForeignKey($column, $rawValue, $catalogCache, $opts, $institutoId, $rowErrors);
        }

        // Int
        if (isset($schema[$column]) && $this->isIntType($schema[$column]['type'])) {
            if ($this->isNotApplicableValue($rawValue)) {
                return null;
            }
            if (!is_numeric($rawValue)) {
                $rowErrors[$column][] = 'Debe ser numérico: ' . $rawValue;
                return null;
            }
            return (int)$rawValue;
        }

        // Email
        if ($column === 'email') {
            if (!filter_var($rawValue, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[$column][] = 'Email inválido: ' . $rawValue;
                return null;
            }
            return $rawValue;
        }

        // Strings
        return is_string($rawValue) ? trim($rawValue) : $rawValue;
    }

    private function resolveForeignKey($column, $value, array $catalogCache, array $opts, $institutoId, array &$rowErrors)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($this->isNotApplicableValue($value)) {
            return null;
        }

        // ID directo
        if (is_numeric($value)) {
            $id = (int)$value;

            // Semestre: aceptar que el CSV traiga el "numero" en vez del id.
            if ($column === 'semestre_id') {
                if (!isset($catalogCache['fk'][$column]['ids'][$id])
                    && isset($catalogCache['fk'][$column]['extra']['numero'][$id])) {
                    return (int)$catalogCache['fk'][$column]['extra']['numero'][$id];
                }
            }

            if (!empty($opts['strict_fks'])) {
                if (!isset($catalogCache['fk'][$column]['ids'][$id])) {
                    $rowErrors[$column][] = 'ID no existe en ' . $this->fkMeta[$column]['table'] . ': ' . $id;
                    return null;
                }
            }

            // Check tenant para TipoBeca
            if ($column === 'tipo_beca_id' && !empty($institutoId)) {
                if (isset($catalogCache['tipoBecaById'][$id])) {
                    $becaInst = (int)$catalogCache['tipoBecaById'][$id];
                    if ((int)$becaInst !== (int)$institutoId) {
                        $rowErrors[$column][] = 'TipoBeca no pertenece al instituto de la encuesta (beca.instituto_id=' . $becaInst . ', encuesta.instituto_id=' . $institutoId . ')';
                        return null;
                    }
                }
            }

            return $id;
        }

        $rawString = (string)$value;
        $nameKey = $this->normalizeCatalogKey($rawString);

        if (isset($this->fkValueAliases[$column]) && array_key_exists($nameKey, $this->fkValueAliases[$column])) {
            $alias = $this->fkValueAliases[$column][$nameKey];
            if ($alias === null) {
                return null;
            }
            $nameKey = (string)$alias;
        }

        if ($column === 'instituto_id') {
            if (isset($catalogCache['fk'][$column]['byName'][$nameKey])) {
                return (int)$catalogCache['fk'][$column]['byName'][$nameKey];
            }

            $normalizedInstituto = str_replace([' ', '_'], '-', $nameKey);
            if (isset($catalogCache['fk'][$column]['byName'][$normalizedInstituto])) {
                return (int)$catalogCache['fk'][$column]['byName'][$normalizedInstituto];
            }
        }

        // Semestre: permitir número si viene como texto
        if ($column === 'semestre_id') {
            if (is_numeric($value) && isset($catalogCache['fk'][$column]['extra']['numero'][(int)$value])) {
                return (int)$catalogCache['fk'][$column]['extra']['numero'][(int)$value];
            }
        }

        // TipoBeca es por instituto
        if ($column === 'tipo_beca_id') {
            if (empty($institutoId)) {
                $rowErrors[$column][] = 'No se puede resolver TipoBeca sin instituto_id';
                return null;
            }

            if (isset($catalogCache['fk'][$column]['byInstituto'][(int)$institutoId][$nameKey])) {
                return (int)$catalogCache['fk'][$column]['byInstituto'][(int)$institutoId][$nameKey];
            }

            $rowErrors[$column][] = 'Valor no encontrado en TipoBeca para instituto_id=' . $institutoId . ': ' . $value;
            return null;
        }

        if (isset($catalogCache['fk'][$column]['byName'][$nameKey])) {
            return (int)$catalogCache['fk'][$column]['byName'][$nameKey];
        }

        // Fallback: cuando el usuario seleccionó múltiples opciones (Forms checkboxes) y vienen separadas por coma/|/;
        // Probamos resolver el primer ítem válido. Esto NO se ejecuta si el valor completo ya matcheó arriba.
        if (preg_match('/[\|,;]+/', $rawString)) {
            $items = $this->splitMultiValue($rawString);
            foreach ($items as $item) {
                $k = $this->normalizeCatalogKey($item);
                if (isset($this->fkValueAliases[$column]) && array_key_exists($k, $this->fkValueAliases[$column])) {
                    $alias = $this->fkValueAliases[$column][$k];
                    if ($alias === null) {
                        continue;
                    }
                    $k = (string)$alias;
                }
                if (isset($catalogCache['fk'][$column]['byName'][$k])) {
                    return (int)$catalogCache['fk'][$column]['byName'][$k];
                }
            }
        }

        $rowErrors[$column][] = 'Valor no encontrado en ' . $this->fkMeta[$column]['table'] . ': ' . $value;
        return null;
    }

    private function resolveRelations(array $relations, array $catalogCache, array $opts, array &$rowErrors)
    {
        $out = [
            'activos' => [],
            'servicios' => [],
            'ambientes' => [],
        ];

        foreach ($relations as $relKey => $relValue) {
            if ($relValue === null || $relValue === '') {
                continue;
            }

            if (!isset($this->relationMeta[$relKey])) {
                continue;
            }

            $table = $this->relationMeta[$relKey]['table'];
            $byName = isset($catalogCache['byTable'][$table]['byName']) ? $catalogCache['byTable'][$table]['byName'] : [];
            $ids = isset($catalogCache['byTable'][$table]['ids']) ? $catalogCache['byTable'][$table]['ids'] : [];

            $items = $this->splitMultiValue($relValue);
            $resolved = [];

            foreach ($items as $item) {
                if ($item === null || $item === '') {
                    continue;
                }

                if (is_numeric($item)) {
                    $id = (int)$item;
                    if (!empty($opts['strict_fks']) && !isset($ids[$id])) {
                        $rowErrors['rel:' . $relKey][] = 'ID no existe en ' . $table . ': ' . $id;
                        continue;
                    }
                    $resolved[] = $id;
                    continue;
                }

                $nameKey = $this->normalizeCatalogKey($item);
                if (isset($byName[$nameKey])) {
                    $resolved[] = (int)$byName[$nameKey];
                } else {
                    $rowErrors['rel:' . $relKey][] = 'Valor no encontrado en ' . $table . ': ' . $item;
                }
            }

            // Dedup
            $resolved = array_values(array_unique($resolved));
            $out[$relKey] = $resolved;
        }

        return $out;
    }

    private function splitMultiValue($value)
    {
        if (!is_string($value)) {
            return [];
        }

        // Soportar varios delimitadores típicos de export (| , ;)
        $raw = preg_split('/\s*[\|,;]+\s*/', $value);

        $out = [];
        foreach ($raw as $v) {
            $vv = trim($v);
            if ($vv !== '') {
                $out[] = $vv;
            }
        }

        return $out;
    }

    private function validateRow(array $data, array $opts, array &$rowErrors)
    {
        $required = $opts['strict_validation']
            ? [
                'email',
                'nombres',
                'apellidos',
                'cedula',
                'telefono',
                'fecha_nacimiento',
                'direccion',
                'hijos',
                'estudio_fya',
                'instituto_id',
                'nacionalidad_id',
                'sexo_id',
                'estado_civil_id',
                'tipo_estudiante_id',
                'carrera_id',
                'semestre_id',
                'veracidad_id',
            ]
            : [
                'email',
                'nombres',
                'apellidos',
                'cedula',
                'instituto_id',
            ];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $rowErrors[$field][] = 'Campo requerido.';
            }
        }

        // Hijos vs numero_hijos
        if (isset($data['hijos']) && (int)$data['hijos'] === 1) {
            if (isset($data['numero_hijos']) && (int)$data['numero_hijos'] < 0) {
                $rowErrors['numero_hijos'][] = 'No puede ser negativo.';
            }
        }
    }

    private function parseBoolean($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value === 0 ? 0 : 1;
        }

        if (is_numeric($value)) {
            return ((int)$value) === 0 ? 0 : 1;
        }

        if (!is_string($value)) {
            return null;
        }

        $v = trim((string)$value);

        // Igual que en normalizeCatalogKey(): evitamos strtolower() sobre UTF-8.
        $noAccents = @iconv('UTF-8', 'ASCII//TRANSLIT', $v);
        if ($noAccents !== false && $noAccents !== null) {
            $v = $noAccents;
        }

        $v = strtolower($v);

        // iconv puede introducir apostrofes; los removemos.
        $v = str_replace(["'", '`', '´', '’'], '', $v);

        // Compat: por si no hubo transliteración.
        $v = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $v);

        $trueVals = ['1', 'si', 'sí', 's', 'true', 't', 'yes', 'y', 'x'];
        $falseVals = ['0', 'no', 'n', 'false', 'f'];

        if (in_array($v, $trueVals, true)) {
            return 1;
        }
        if (in_array($v, $falseVals, true)) {
            return 0;
        }

        return null;
    }

    private function parseDate($value)
    {
        if ($value === null) {
            return null;
        }

        $v = trim((string)$value);
        if ($v === '') {
            return null;
        }

        // Excel serial
        if (is_numeric($v) && (int)$v > 10000) {
            try {
                $base = new DateTime('1899-12-30');
                $base->add(new DateInterval('P' . (int)$v . 'D'));
                return $base->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
        foreach ($formats as $f) {
            $dt = DateTime::createFromFormat($f, $v);
            if ($dt instanceof DateTime) {
                $errors = DateTime::getLastErrors();
                if (is_array($errors) && $errors['warning_count'] === 0 && $errors['error_count'] === 0) {
                    return $dt->format('Y-m-d');
                }
            }
        }

        return null;
    }

    private function parseDateTime($value)
    {
        if ($value === null) {
            return null;
        }

        $v = trim((string)$value);
        if ($v === '') {
            return null;
        }

        // ISO 8601
        $v = str_replace('T', ' ', $v);

        $formats = ['Y-m-d H:i:s', 'Y-m-d H:i', 'd/m/Y H:i:s', 'd/m/Y H:i'];
        foreach ($formats as $f) {
            $dt = DateTime::createFromFormat($f, $v);
            if ($dt instanceof DateTime) {
                $errors = DateTime::getLastErrors();
                if (is_array($errors) && $errors['warning_count'] === 0 && $errors['error_count'] === 0) {
                    return $dt->format('Y-m-d H:i:s');
                }
            }
        }

        // Fallback: parser libre
        try {
            $dt = new DateTime($v);
            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }

    private function isDuplicateCedulaError(PDOException $e)
    {
        if (!isset($e->errorInfo) || !is_array($e->errorInfo)) {
            return false;
        }

        // MySQL duplicate entry => 1062
        return isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062;
    }

    private function isIntType($type)
    {
        if (!is_string($type)) {
            return false;
        }
        return stripos($type, 'int') !== false && stripos($type, 'tinyint') === false;
    }

    private function isTinyIntType($type)
    {
        if (!is_string($type)) {
            return false;
        }
        return stripos($type, 'tinyint') !== false;
    }
}
