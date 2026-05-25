<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ReportesRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getDashboardGeneral(array $filters = [])
    {
        $scope = $this->buildScopeWhere($filters, false);
        
        $kpisSql = "SELECT
                COUNT(*) AS total_encuestados,
                ROUND(COUNT(*) * 1.28) AS total_poblacion,
                COALESCE(ROUND((COUNT(*) / NULLIF(ROUND(COUNT(*) * 1.28), 0)) * 100, 2), 0) AS tasa_respuesta,
                COALESCE(ROUND(AVG(
                    CASE
                        WHEN e.inicio IS NOT NULL AND e.creado IS NOT NULL AND e.creado >= e.inicio
                            THEN TIMESTAMPDIFF(SECOND, e.inicio, e.creado) / 60
                        ELSE NULL
                    END
                ), 2), 0) AS tiempo_promedio_respuesta_minutos,
                SUM(
                    CASE
                        WHEN e.inicio IS NOT NULL AND e.creado IS NOT NULL AND e.creado >= e.inicio
                            THEN 1
                        ELSE 0
                    END
                ) AS encuestas_con_tiempo
            FROM Encuesta e
            LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
            LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
            LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
            LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
            " . $scope['where'];

        // error_log('Ejecutando consulta kpis con SQL: ' . $kpisSql , 3, __DIR__ . '/debug_kpis.log');
        // error_log('Con bindings: ' . json_encode($scope['bindings']) , 3, __DIR__ . '/debug_kpis.log');
        $kpis = $this->fetchOne($kpisSql, $scope['bindings']);

        $diaMasEncuestasSql = "SELECT
                DATE(e.creado) AS fecha,
                COUNT(*) AS total
            FROM Encuesta e
            LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
            LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
            LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
            LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
            " . $scope['where'] . "
            GROUP BY DATE(e.creado)
            ORDER BY total DESC, fecha ASC
            LIMIT 1";

        $diaMasEncuestas = $this->fetchOne($diaMasEncuestasSql, $scope['bindings']);

        $modaSql = "SELECT estrato, total
            FROM (
                SELECT
                    CASE
                        WHEN (e.tipo_vivienda_id IS NOT NULL
                              AND e.fuente_ingreso_familiar_id IS NOT NULL
                              AND e.nivel_eduacion_padre_id IS NOT NULL
                              AND e.nivel_eduacion_madre_id IS NOT NULL) THEN
                            CASE
                                WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 6 THEN 1
                                WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 9 THEN 2
                                WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 12 THEN 3
                                WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 16 THEN 4
                                ELSE 5
                            END
                        ELSE NULL
                    END AS estrato,
                    COUNT(*) AS total
                FROM Encuesta e
                LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
                LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
                LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
                LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
                " . $scope['where'] . "
                GROUP BY estrato
            ) t
            WHERE estrato IS NOT NULL
            ORDER BY total DESC, estrato ASC
            LIMIT 1";

        $moda = $this->fetchOne($modaSql, $scope['bindings']);

        $sexoSql = "SELECT
                COALESCE(s.nombre, 'Sin dato') AS label,
                COUNT(*) AS value
            FROM Encuesta e
            LEFT JOIN Sexo s ON s.id = e.sexo_id
            LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
            LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
            LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
            LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
            " . $scope['where'] . "
            GROUP BY COALESCE(s.nombre, 'Sin dato')
            ORDER BY value DESC, label ASC";

        $sexoRows = $this->fetchAll($sexoSql, $scope['bindings']);

        $estratosSql = "SELECT
                CASE
                    WHEN (e.tipo_vivienda_id IS NOT NULL
                          AND e.fuente_ingreso_familiar_id IS NOT NULL
                          AND e.nivel_eduacion_padre_id IS NOT NULL
                          AND e.nivel_eduacion_madre_id IS NOT NULL) THEN
                        CASE
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 6 THEN '1'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 9 THEN '2'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 12 THEN '3'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 16 THEN '4'
                            ELSE '5'
                        END
                    ELSE 'Sin dato'
                END AS label,
                COUNT(*) AS value
            FROM Encuesta e
            LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
            LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
            LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
            LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
            " . $scope['where'] . "
            GROUP BY label
            ORDER BY label ASC";

        $estratosRows = $this->fetchAll($estratosSql, $scope['bindings']);

        return [
            'kpis' => [
                'total_encuestados' => isset($kpis['total_encuestados']) ? (int)$kpis['total_encuestados'] : 0,
                'total_poblacion' => isset($kpis['total_poblacion']) ? (int)$kpis['total_poblacion'] : 0,
                'tasa_respuesta' => isset($kpis['tasa_respuesta']) ? (float)$kpis['tasa_respuesta'] : 0,
                'tiempo_promedio_respuesta_minutos' => isset($kpis['tiempo_promedio_respuesta_minutos']) ? (float)$kpis['tiempo_promedio_respuesta_minutos'] : 0,
                'encuestas_con_tiempo' => isset($kpis['encuestas_con_tiempo']) ? (int)$kpis['encuestas_con_tiempo'] : 0,
                'dia_mas_encuestas_fecha' => isset($diaMasEncuestas['fecha']) && $diaMasEncuestas['fecha'] !== null ? (string)$diaMasEncuestas['fecha'] : null,
                'dia_mas_encuestas_total' => isset($diaMasEncuestas['total']) ? (int)$diaMasEncuestas['total'] : 0,
                'moda_estrato' => isset($moda['estrato']) && $moda['estrato'] !== null ? (string)$moda['estrato'] : null,
            ],
            'sexo' => $this->toSeries($sexoRows),
            'estratos' => $this->toSeries($estratosRows),
        ];
    }

    public function getAnalisisAcademico(array $filters = [])
    {
        $scope = $this->buildScopeWhere($filters, false);

        $sql = "SELECT
                c.id AS carrera_id,
                c.nombre AS carrera,
                CASE
                    WHEN (e.tipo_vivienda_id IS NOT NULL
                          AND e.fuente_ingreso_familiar_id IS NOT NULL
                          AND e.nivel_eduacion_padre_id IS NOT NULL
                          AND e.nivel_eduacion_madre_id IS NOT NULL) THEN
                        CASE
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 6 THEN '1'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 9 THEN '2'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 12 THEN '3'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 16 THEN '4'
                            ELSE '5'
                        END
                    ELSE 'Sin dato'
                END AS estrato,
                COUNT(*) AS total
            FROM Encuesta e
            LEFT JOIN Carrera c ON c.id = e.carrera_id
            LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
            LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
            LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
            LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
            " . $scope['where'] . "
            GROUP BY c.id, c.nombre, estrato
            ORDER BY c.nombre ASC, estrato ASC";

        $rows = $this->fetchAll($sql, $scope['bindings']);

        $labels = [];
        $byCarrera = [];
        $estratos = ['1', '2', '3', '4', '5', 'Sin dato'];

        foreach ($rows as $row) {
            $carrera = isset($row['carrera']) && $row['carrera'] !== null ? (string)$row['carrera'] : 'Sin carrera';
            $estrato = isset($row['estrato']) ? (string)$row['estrato'] : 'Sin dato';
            $total = isset($row['total']) ? (int)$row['total'] : 0;

            if (!isset($byCarrera[$carrera])) {
                $byCarrera[$carrera] = [];
                foreach ($estratos as $e) {
                    $byCarrera[$carrera][$e] = 0;
                }
                $labels[] = $carrera;
            }

            if (!isset($byCarrera[$carrera][$estrato])) {
                $byCarrera[$carrera][$estrato] = 0;
            }

            $byCarrera[$carrera][$estrato] += $total;
        }

        $datasets = [];
        foreach ($estratos as $estrato) {
            $series = [];
            $total = [];
            foreach ($labels as $carrera) {

                $totalCarrera = array_sum($byCarrera[$carrera]);
                $value = $byCarrera[$carrera][$estrato];
                $total[] = $value > 0 ? $value : 0;
                $series[] = $totalCarrera > 0 ? round(($value / $totalCarrera) * 100, 2) : 0;
            }
            $datasets[] = [
                'label' => $estrato === 'Sin dato' ? 'Sin dato' : 'Estrato ' . $estrato,
                'key' => $estrato,
                'values' => ['series' => $series, 'totals' => $total],
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    public function getDemograficoVulnerabilidad(array $filters = [])
    {
        $scope = $this->buildScopeWhere($filters, false);

        $heatmapSql = "SELECT
                COALESCE(c.nombre, 'Sin carrera') AS carrera,
                CASE
                    WHEN (e.tipo_vivienda_id IS NOT NULL
                          AND e.fuente_ingreso_familiar_id IS NOT NULL
                          AND e.nivel_eduacion_padre_id IS NOT NULL
                          AND e.nivel_eduacion_madre_id IS NOT NULL) THEN
                        CASE
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 6 THEN '1'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 9 THEN '2'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 12 THEN '3'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 16 THEN '4'
                            ELSE '5'
                        END
                    ELSE 'Sin dato'
                END AS estrato,
                COUNT(*) AS total
            FROM Encuesta e
            LEFT JOIN Carrera c ON c.id = e.carrera_id
            LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
            LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
            LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
            LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
            " . $scope['where'] . "
            GROUP BY carrera, estrato
            ORDER BY carrera ASC, estrato ASC";

        $heatmapRows = $this->fetchAll($heatmapSql, $scope['bindings']);

        $columns = ['1', '2', '3', '4', '5', 'Sin dato'];
        $rows = [];
        $matrix = [];

        foreach ($heatmapRows as $item) {
            $carrera = (string)$item['carrera'];
            $estrato = (string)$item['estrato'];
            $total = (int)$item['total'];

            if (!isset($matrix[$carrera])) {
                $matrix[$carrera] = [];
                foreach ($columns as $col) {
                    $matrix[$carrera][$col] = 0;
                }
                $rows[] = $carrera;
            }

            if (!isset($matrix[$carrera][$estrato])) {
                $matrix[$carrera][$estrato] = 0;
            }

            $matrix[$carrera][$estrato] += $total;
        }

        $values = [];
        foreach ($rows as $carrera) {
            $line = [];
            foreach ($columns as $col) {
                $line[] = $matrix[$carrera][$col];
            }
            $values[] = $line;
        }

        $sexoEstratoSql = "SELECT
                CASE
                    WHEN (e.tipo_vivienda_id IS NOT NULL
                          AND e.fuente_ingreso_familiar_id IS NOT NULL
                          AND e.nivel_eduacion_padre_id IS NOT NULL
                          AND e.nivel_eduacion_madre_id IS NOT NULL) THEN
                        CASE
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 6 THEN '1'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 9 THEN '2'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 12 THEN '3'
                            WHEN (COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0)) <= 16 THEN '4'
                            ELSE '5'
                        END
                    ELSE 'Sin dato'
                END AS estrato,
                LOWER(COALESCE(s.nombre, 'sin dato')) AS sexo,
                COUNT(*) AS total
            FROM Encuesta e
            LEFT JOIN Sexo s ON s.id = e.sexo_id
            LEFT JOIN TipoVivienda tv ON tv.id = e.tipo_vivienda_id
            LEFT JOIN FuenteIngresoFamiliar fif ON fif.id = e.fuente_ingreso_familiar_id
            LEFT JOIN NivelEducacion nep ON nep.id = e.nivel_eduacion_padre_id
            LEFT JOIN NivelEducacion nem ON nem.id = e.nivel_eduacion_madre_id
            " . $scope['where'] . "
            GROUP BY estrato, sexo
            ORDER BY estrato ASC, sexo ASC";

        $sexoRows = $this->fetchAll($sexoEstratoSql, $scope['bindings']);

        $femenino = [];
        $masculino = [];
        foreach ($columns as $col) {
            $femenino[$col] = 0;
            $masculino[$col] = 0;
        }

        foreach ($sexoRows as $row) {
            $estrato = (string)$row['estrato'];
            $sexo = (string)$row['sexo'];
            $total = (int)$row['total'];

            if (!isset($femenino[$estrato])) {
                $femenino[$estrato] = 0;
            }
            if (!isset($masculino[$estrato])) {
                $masculino[$estrato] = 0;
            }

            if (strpos($sexo, 'femen') !== false) {
                $femenino[$estrato] += $total;
            } elseif (strpos($sexo, 'mascul') !== false) {
                $masculino[$estrato] += $total;
            }
        }

        return [
            'heatmap' => [
                'rows' => $rows,
                'columns' => $columns,
                'values' => $values,
            ],
            'sexo_por_estrato' => [
                'labels' => $columns,
                'femenino' => array_values($femenino),
                'masculino' => array_values($masculino),
            ],
        ];
    }

    public function getFiltros(array $filters = [])
    {
        $scope = $this->buildScopeWhere($filters, true);
        // error_log('holiiii ------------- probando filtros', 3, __DIR__ . '/debug_filtros.log');
        // error_log(print_r($scope, true), 3, __DIR__ . '/debug_filtros.log');

        $institutos = $this->fetchAll(
            "SELECT id, nombre, siglas
             FROM Instituto
             WHERE activo = 1
             ORDER BY nombre ASC"
        );

        $sqlCarreras = "SELECT DISTINCT c.id, c.nombre
            FROM Carrera c
            INNER JOIN Instituto_carrera ic ON ic.carrera_id = c.id
            " . $scope['where'] . "
            ORDER BY c.nombre ASC ";

        // error_log('SQL Carreras: ' . $sqlCarreras, 3, __DIR__ . '/debug_filtros.log');

        $carreras = $this->fetchAll($sqlCarreras, $scope['bindings']);

        return [
            'institutos' => array_values(array_map(function ($row) {
                return [
                    'id' => (int)$row['id'],
                    'nombre' => (string)$row['nombre'],
                    'siglas' => isset($row['siglas']) ? (string)$row['siglas'] : '',
                ];
            }, $institutos)),
            'facultades' => [],
            'carreras' => array_values(array_map(function ($row) {
                return [
                    'id' => (int)$row['id'],
                    'nombre' => (string)$row['nombre'],
                ];
            }, $carreras)),
            'estratos' => ['1', '2', '3', '4', '5', 'Sin dato'],
        ];
    }

    private function buildScopeWhere(array $filters = [], $withDerivedJoins = false)
    {
        $where = [ !$withDerivedJoins ? 'e.activo = 1' : 'ic.activo = 1'];
        $bindings = [];

        // error_log('Construyendo scope WHERE con filtros: ' . print_r($where, true), 3, __DIR__ . '/debug_build.log');

        if (!empty($filters['instituto_id']) && is_numeric($filters['instituto_id'])) {
            $where[] =  !$withDerivedJoins ? 'e.instituto_id = :instituto_id' : 'ic.instituto_id = :instituto_id';
            $bindings['instituto_id'] = (int)$filters['instituto_id'];
        }

        if (!empty($filters['carrera_id']) && is_numeric($filters['carrera_id'])) {
            $where[] = 'e.carrera_id = :carrera_id';
            $bindings['carrera_id'] = (int)$filters['carrera_id'];
        }

        if (!empty($filters['from']) && $this->isValidDate($filters['from'])) {
            $where[] = 'DATE(e.creado) >= :from_date';
            $bindings['from_date'] = (string)$filters['from'];
        }

        if (!empty($filters['to']) && $this->isValidDate($filters['to'])) {
            $where[] = 'DATE(e.creado) <= :to_date';
            $bindings['to_date'] = (string)$filters['to'];
        }

        return [
            'where' => 'WHERE ' . implode(' AND ', $where),
            'bindings' => $bindings,
        ];
    }

    private function toSeries(array $rows)
    {
        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $labels[] = (string)$row['label'];
            $values[] = (int)$row['value'];
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function fetchAll($sql, array $bindings = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchOne($sql, array $bindings = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : [];
    }

    private function isValidDate($value)
    {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$dt) {
            return false;
        }

        $errors = \DateTime::getLastErrors();
        if (!empty($errors['warning_count']) || !empty($errors['error_count'])) {
            return false;
        }

        return true;
    }
}
