<?php

namespace App\Services;

use App\Core\Database;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\StringUtilities;

class ExportService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function exportarEncuestasExcel(array $filters = [], array $institutoInfo = [])
    {
        $data = $this->getEncuestasData($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Encuestas');

        $this->agregarMembrete($sheet, $institutoInfo);

        $headerRow = 6;
        $headers = [
            'A' . $headerRow => 'ID',
            'B' . $headerRow => 'Estudiante',
            'C' . $headerRow => 'Cédula',
            'D' . $headerRow => 'Carrera',
            'E' . $headerRow => 'Instituto',
            'F' . $headerRow => 'Fecha',
            'G' . $headerRow => 'Estrato',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        $row = $headerRow + 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['id']);
            $sheet->setCellValue('B' . $row, $item['estudiante']);
            $sheet->setCellValue('C' . $row, $item['cedula']);
            $sheet->setCellValue('D' . $row, $item['carrera']);
            $sheet->setCellValue('E' . $row, $item['instituto']);
            $sheet->setCellValue('F' . $row, $item['creado']);
            $sheet->setCellValue('G' . $row, $item['estrato']);
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A' . $headerRow . ':G' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        if ($tempFile === false) {
            throw new \Exception('No se pudo crear archivo temporal');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $tempFile;
    }

    private function agregarMembrete($sheet, array $institutoInfo)
    {
        $nombre = $institutoInfo['nombre'] ?? null;
        $siglas = $institutoInfo['siglas'] ?? null;
        $rol = $institutoInfo['rol'] ?? null;

        if (empty($nombre)) {
            if ($rol === 'SUPER_ADMIN') {
                $nombre = 'Consolidado General';
                $siglas = 'Todos los Institutos';
            } else {
                $nombre = 'Institución';
                $siglas = '';
            }
        }

        $nombreCompleto = !empty($siglas) ? $nombre . ' (' . $siglas . ')' : $nombre;

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $nombreCompleto);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Reporte de Encuestas Socioeconómicas');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $fechaGeneracion = date('d M Y, h:i A');
        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A3', 'Generado: ' . $fechaGeneracion);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A4:G4');
        $sheet->setCellValue('A4', '');

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(10);

        $sheet->getStyle('A1:G4')->applyFromArray([
            'borders' => [
                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM],
            ],
        ]);

        for ($i = 1; $i <= 4; $i++) {
            $cell = 'A' . $i;
            $sheet->getStyle($cell)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F9FAFB'],
                ],
            ]);
        }
    }

    private function getEncuestasData(array $filters)
    {
        $withInstitutoFilter = false;
        $extraWhere = [];
        $bindings = [];

        if (!empty($filters['instituto_id'])) {
            $withInstitutoFilter = true;
            $bindings['instituto_id'] = (int)$filters['instituto_id'];
        }

        if (!empty($filters['carrera_id']) && is_numeric($filters['carrera_id'])) {
            $extraWhere[] = 'e.carrera_id = :carrera_id';
            $bindings['carrera_id'] = (int)$filters['carrera_id'];
        }

        $estratoParam = null;
        if (isset($filters['estrato'])) {
            $estratoParam = $filters['estrato'];
        }

        if ($estratoParam !== null) {
            $estratoRaw = strtolower(trim((string)$estratoParam));
            $esCompletaSql = '(e.tipo_vivienda_id IS NOT NULL AND e.fuente_ingreso_familiar_id IS NOT NULL AND e.nivel_eduacion_padre_id IS NOT NULL AND e.nivel_eduacion_madre_id IS NOT NULL)';
            $esIncompletaSql = '(e.tipo_vivienda_id IS NULL OR e.fuente_ingreso_familiar_id IS NULL OR e.nivel_eduacion_padre_id IS NULL OR e.nivel_eduacion_madre_id IS NOT NULL)';
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

        if (!empty($filters['q'])) {
            $q = trim((string)$filters['q']);
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

        $puntajeSql = "(COALESCE(tv.valor_estrato, 0) + COALESCE(fif.valor_estrato, 0) + COALESCE(nep.valor_estrato, 0) + COALESCE(nem.valor_estrato, 0))";
        $puntajeCompletoSql = "(e.tipo_vivienda_id IS NOT NULL AND e.fuente_ingreso_familiar_id IS NOT NULL AND e.nivel_eduacion_padre_id IS NOT NULL AND e.nivel_eduacion_madre_id IS NOT NULL)";

        $sql = "SELECT
                    e.id,
                    e.creado,
                    e.nombres,
                    e.apellidos,
                    CONCAT(e.nombres, ' ', e.apellidos) AS estudiante,
                    e.cedula,
                    c.nombre AS carrera,
                    i.siglas AS instituto,
                    e.correo,
                    e.telefono

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
        $sql .= " LIMIT 10000";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
}