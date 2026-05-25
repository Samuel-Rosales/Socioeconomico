<?php

namespace App\Services;

use App\Repositories\ReportesRepository;
use Exception;

class ReportesService
{
    private $repository;

    public function __construct()
    {
        $this->repository = new ReportesRepository();
    }

    public function dashboardGeneral(array $filters = [])
    {
        try {
            return [
                'success' => true,
                'data' => $this->repository->getDashboardGeneral($filters),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Error al generar reporte de dashboard general',
                'errors' => ['database' => [$e->getMessage()]],
            ];
        }
    }

    public function analisisAcademico(array $filters = [])
    {
        try {
            return [
                'success' => true,
                'data' => $this->repository->getAnalisisAcademico($filters),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Error al generar reporte de analisis academico',
                'errors' => ['database' => [$e->getMessage()]],
            ];
        }
    }

    public function demograficoVulnerabilidad(array $filters = [])
    {
        try {
            return [
                'success' => true,
                'data' => $this->repository->getDemograficoVulnerabilidad($filters),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Error al generar reporte demografico de vulnerabilidad',
                'errors' => ['database' => [$e->getMessage()]],
            ];
        }
    }

    public function filtros(array $filters = [])
    {
        try {
            return [
                'success' => true,
                'data' => $this->repository->getFiltros($filters),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Error al cargar filtros de reportes',
                'errors' => ['database' => [$e->getMessage()]],
            ];
        }
    }
}
