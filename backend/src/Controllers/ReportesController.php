<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\TenantContext;
use App\Services\ReportesService;

class ReportesController
{
    private $service;

    public function __construct()
    {
        $this->service = new ReportesService();
    }

    public function dashboardGeneral($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN', 'ADMIN_SEDE', 'ANALISTA']);
        $filters = $this->resolveFilters($actor, true);

        $result = $this->service->dashboardGeneral($filters);
        $this->respond($result, 'Reporte dashboard general generado correctamente');
    }

    public function analisisAcademico($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN', 'ADMIN_SEDE', 'ANALISTA']);
        $filters = $this->resolveFilters($actor, true);

        $result = $this->service->analisisAcademico($filters);
        $this->respond($result, 'Reporte de analisis academico generado correctamente');
    }

    public function demograficoVulnerabilidad($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN', 'ADMIN_SEDE', 'ANALISTA']);
        $filters = $this->resolveFilters($actor, true);

        $result = $this->service->demograficoVulnerabilidad($filters);
        $this->respond($result, 'Reporte demografico y vulnerabilidad generado correctamente');
    }

    public function filtros($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN', 'ADMIN_SEDE', 'ANALISTA']);
        $filters = $this->resolveFilters($actor, false);

        $result = $this->service->filtros($filters);
        $this->respond($result, 'Filtros de reportes obtenidos correctamente');
    }

    private function resolveFilters(array $actor, $withDates)
    {
        $institutoId = null;

        if (isset($actor['rol']) && $actor['rol'] === 'SUPER_ADMIN') {
            $institutoId = TenantContext::resolveInstitutoId(null, false);
        } else {
            $institutoId = isset($actor['instituto_id']) ? $actor['instituto_id'] : null;
        }

        $filters = [
            'instituto_id' => $institutoId,
        ];

        if (isset($_GET['carrera_id']) && is_numeric($_GET['carrera_id']) && (int)$_GET['carrera_id'] > 0) {
            $filters['carrera_id'] = (int)$_GET['carrera_id'];
        }

        // Se acepta por contrato, aunque actualmente no existe entidad Facultad en el schema.
        if (isset($_GET['facultad_id']) && is_numeric($_GET['facultad_id']) && (int)$_GET['facultad_id'] > 0) {
            $filters['facultad_id'] = (int)$_GET['facultad_id'];
        }

        if ($withDates) {
            if (isset($_GET['from'])) {
                $filters['from'] = (string)$_GET['from'];
            }
            if (isset($_GET['to'])) {
                $filters['to'] = (string)$_GET['to'];
            }
        }

        return $filters;
    }

    private function respond(array $result, $successMessage)
    {
        if (!empty($result['success'])) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result['data'],
                'message' => $successMessage,
            ]);
            return;
        }

        http_response_code(isset($result['status']) ? (int)$result['status'] : 400);
        echo json_encode([
            'success' => false,
            'data' => [
                'errors' => isset($result['errors']) ? $result['errors'] : [],
            ],
            'message' => isset($result['message']) ? $result['message'] : 'Error en modulo de reportes',
        ]);
    }
}
