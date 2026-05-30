<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\TenantContext;
use App\Services\ExportService;
use App\Models\InstitutoModel;

class ExportController
{
    private $service;

    public function __construct()
    {
        $this->service = new ExportService();
    }

    public function exportarEncuestasExcel($params = [])
    {
        $actor = Auth::requireAuth(['SUPER_ADMIN', 'ADMIN_SEDE', 'ANALISTA']);

        $institutoId = null;
        $institutoNombre = null;
        $institutoSiglas = null;

        if (isset($actor['rol']) && $actor['rol'] === 'SUPER_ADMIN') {
            $institutoId = TenantContext::resolveInstitutoId(null, false);
        } elseif (isset($actor['instituto_id'])) {
            $institutoId = $actor['instituto_id'];
        }

        if (!empty($institutoId)) {
            $institutoModel = new InstitutoModel();
            $stmt = $institutoModel->getAllConEstadoEncuesta();
            foreach ($stmt as $inst) {
                if ((int)$inst['id'] === (int)$institutoId) {
                    $institutoNombre = $inst['nombre'];
                    $institutoSiglas = $inst['siglas'];
                    break;
                }
            }
        }

        $filters = [];

        if (!empty($institutoId)) {
            $filters['instituto_id'] = $institutoId;
        }

        if (isset($_GET['q']) && is_string($_GET['q'])) {
            $q = trim($_GET['q']);
            if ($q !== '') {
                $filters['q'] = $q;
            }
        }

        if (isset($_GET['carrera_id']) && is_numeric($_GET['carrera_id']) && (int)$_GET['carrera_id'] > 0) {
            $filters['carrera_id'] = (int)$_GET['carrera_id'];
        }

        if (isset($_GET['estrato']) && is_string($_GET['estrato'])) {
            $estrato = trim($_GET['estrato']);
            if ($estrato !== '') {
                $filters['estrato'] = $estrato;
            }
        }

        if (isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 0) {
            $filters['page'] = (int)$_GET['page'];
        }

        if (isset($_GET['per_page']) && is_numeric($_GET['per_page']) && (int)$_GET['per_page'] > 0) {
            $filters['per_page'] = (int)$_GET['per_page'];
        }

        if (isset($_GET['instituto_id']) && is_numeric($_GET['instituto_id']) && (int)$_GET['instituto_id'] > 0) {
            if ($actor['rol'] === 'SUPER_ADMIN') {
                $filters['instituto_id'] = (int)$_GET['instituto_id'];

                $institutoModel = new InstitutoModel();
                $stmt = $institutoModel->getAllConEstadoEncuesta();
                foreach ($stmt as $inst) {
                    if ((int)$inst['id'] === (int)$_GET['instituto_id']) {
                        $institutoNombre = $inst['nombre'];
                        $institutoSiglas = $inst['siglas'];
                        break;
                    }
                }
            }
        }

        $institutoInfo = [
            'nombre' => $institutoNombre,
            'siglas' => $institutoSiglas,
            'rol' => $actor['rol'] ?? null,
        ];

        try {
            $tempFile = $this->service->exportarEncuestasExcel($filters, $institutoInfo);

            $filename = 'encuestas_' . date('Y-m-d_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tempFile));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            readfile($tempFile);
            unlink($tempFile);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar el archivo Excel: ' . $e->getMessage(),
            ]);
            exit;
        }
    }
}