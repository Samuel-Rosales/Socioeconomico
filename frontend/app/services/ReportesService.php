<?php

namespace App\Services;

class ReportesService
{
    private $api;

    public function __construct(ApiService $api = null)
    {
        $this->api = $api ?: new ApiService();
    }

    public function getDashboardGeneral(array $params = [])
    {
        //echo 'ReportesService::getDashboardGeneral - Params: ' . json_encode($params) . "\n";
        return $this->api->get('/reportes/dashboard-general', $params);

    }

    public function getAnalisisAcademico(array $params = [])
    {
        // echo 'ReportesService::getAnalisisAcademico - Params: ' . json_encode($params) . "\n";
        return $this->api->get('/reportes/analisis-academico', $params);
    }

    public function getDemograficoVulnerabilidad(array $params = [])
    {
        // echo 'ReportesService::getDemograficoVulnerabilidad - Params: ' . json_encode($params) . "\n";
        return $this->api->get('/reportes/demografico-vulnerabilidad', $params);
    }

    public function getFiltros(array $params = [])
    {   
        unset($params['carrera_id']);

        //echo 'ReportesService::getFiltros - Params: ' . json_encode($params) . "\n";
        return $this->api->get('/reportes/filtros', $params);
    }
}
