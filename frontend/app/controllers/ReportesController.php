<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\ApiService;
use App\Services\ReportesService;

class ReportesController extends Controller
{
    private $reportesService;

    public function __construct()
    {
        $api = new ApiService();
        $this->reportesService = new ReportesService($api);
    }

    public function dashboardGeneral()
    {
        $this->checkAuth();

        $context = $this->buildViewContext();
        $reportResponse = $this->reportesService->getDashboardGeneral($context['api_filters']);
        $report = $this->unwrapPayload($reportResponse);

        $this->view('admin/reportes/dashboard_general', [
            'title' => 'Reportes · Dashboard General',
            'current_page' => 'reportes_dashboard_general',
            'report_data' => $report['data'],
            'api_error' => $report['error'],
            'filtros' => $context['filtros'],
            'filtros_catalogo' => $context['filtros_catalogo'],
            'is_super_admin' => $context['is_super_admin'],
            'filtros_action' => BASE_URL . '/admin/reportes/dashboard-general',
            'report_view_key' => 'dashboard-general',
        ], 'admin');
    }

    public function analisisAcademico()
    {
        $this->checkAuth();

        $context = $this->buildViewContext();
        $reportResponse = $this->reportesService->getAnalisisAcademico($context['api_filters']);
        $report = $this->unwrapPayload($reportResponse);

        $this->view('admin/reportes/analisis_academico', [
            'title' => 'Reportes · Analisis Academico',
            'current_page' => 'reportes_analisis_academico',
            'report_data' => $report['data'],
            'api_error' => $report['error'],
            'filtros' => $context['filtros'],
            'filtros_catalogo' => $context['filtros_catalogo'],
            'is_super_admin' => $context['is_super_admin'],
            'filtros_action' => BASE_URL . '/admin/reportes/analisis-academico',
            'report_view_key' => 'analisis-academico',
        ], 'admin');
    }

    public function demograficoVulnerabilidad()
    {
        $this->checkAuth();

        $context = $this->buildViewContext();
        $reportResponse = $this->reportesService->getDemograficoVulnerabilidad($context['api_filters']);
        $report = $this->unwrapPayload($reportResponse);

        $this->view('admin/reportes/demografico_vulnerabilidad', [
            'title' => 'Reportes · Demografico y Vulnerabilidad',
            'current_page' => 'reportes_demografico_vulnerabilidad',
            'report_data' => $report['data'],
            'api_error' => $report['error'],
            'filtros' => $context['filtros'],
            'filtros_catalogo' => $context['filtros_catalogo'],
            'is_super_admin' => $context['is_super_admin'],
            'filtros_action' => BASE_URL . '/admin/reportes/demografico-vulnerabilidad',
            'report_view_key' => 'demografico-vulnerabilidad',
        ], 'admin');
    }

    private function checkAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect(BASE_URL . '/login');
            exit;
        }
    }

    private function isAuthenticated()
    {
        return $this->hasValidAuthSession();
    }

    private function actorRolCodigo()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $authUser = isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']) ? $_SESSION['auth_user'] : [];
        if (isset($authUser['rol']) && is_array($authUser['rol']) && !empty($authUser['rol']['codigo'])) {
            $rol = (string)$authUser['rol']['codigo'];
            $this->closeSession();
            return $rol;
        }

        $this->closeSession();
        return null;
    }

    private function actorInstitutoId()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $authUser = isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']) ? $_SESSION['auth_user'] : [];
        if (isset($authUser['instituto']) && is_array($authUser['instituto']) && !empty($authUser['instituto']['id'])) {
            $institutoId = (int)$authUser['instituto']['id'];
            $this->closeSession();
            return $institutoId;
        }

        if (!empty($authUser['instituto_id'])) {
            $institutoId = (int)$authUser['instituto_id'];
            $this->closeSession();
            return $institutoId;
        }

        $this->closeSession();
        return null;
    }

    private function buildViewContext()
    {
        $rol = $this->actorRolCodigo();
        $isSuperAdmin = ($rol === 'SUPER_ADMIN');

        $from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';
        $institutoId = isset($_GET['instituto_id']) && is_numeric($_GET['instituto_id']) ? (int)$_GET['instituto_id'] : null;
        $carreraId = isset($_GET['carrera_id']) && is_numeric($_GET['carrera_id']) ? (int)$_GET['carrera_id'] : null;

        if (!$isSuperAdmin) {
            $institutoId = $this->actorInstitutoId();
        }

        $apiFilters = [];

        if ($from !== '') {
            $apiFilters['from'] = $from;
        }
        if ($to !== '') {
            $apiFilters['to'] = $to;
        }
        if ($institutoId !== null && $institutoId > 0) {
            $apiFilters['instituto_id'] = $institutoId;
        }
        if ($carreraId !== null && $carreraId > 0) {
            $apiFilters['carrera_id'] = $carreraId;
        }

        $this->closeSession();
        
        // echo 'ReportesController::buildViewContext - API Filters: ' . json_encode($apiFilters) . "\n";
        
        $filtrosResponse = $this->reportesService->getFiltros($apiFilters);
        //echo 'ReportesController::buildViewContext - Filtros Response: ' . json_encode($filtrosResponse) . "\n";
        $filtrosPayload = $this->unwrapPayload($filtrosResponse);
        
        return [
            'is_super_admin' => $isSuperAdmin,
            'filtros' => [
                'from' => $from,
                'to' => $to,
                'instituto_id' => $institutoId,
                'carrera_id' => $carreraId,
            ],
            'api_filters' => $apiFilters,
            'filtros_catalogo' => is_array($filtrosPayload['data']) ? $filtrosPayload['data'] : [],
        ];
    }

    private function unwrapPayload($serviceResponse)
    {
        $result = [
            'data' => null,
            'error' => null,
        ];

        if (!is_array($serviceResponse) || empty($serviceResponse['success'])) {
            $result['error'] = [
                'status' => isset($serviceResponse['status']) ? (int)$serviceResponse['status'] : 0,
                'message' => 'No se pudo conectar con el backend de reportes.',
            ];
            return $result;
        }

        $payload = isset($serviceResponse['data']) && is_array($serviceResponse['data']) ? $serviceResponse['data'] : [];

        if (empty($payload['success'])) {
            $message = isset($payload['message']) ? (string)$payload['message'] : 'Error al consultar reportes.';
            $result['error'] = [
                'status' => isset($serviceResponse['status']) ? (int)$serviceResponse['status'] : 400,
                'message' => $message,
            ];
            return $result;
        }

        $result['data'] = isset($payload['data']) ? $payload['data'] : null;
        return $result;
    }
}
