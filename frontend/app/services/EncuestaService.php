<?php

namespace App\Services;

class EncuestaService
{
    private $api;

    public function __construct(ApiService $api = null)
    {
        $this->api = $api ?: new ApiService();
    }

    public function listarResumen(array $params = [])
    {
        return $this->api->get('/encuesta', $params);
    }

    public function totalPorFiltro($estrato)
    {
        $params = [
            'page' => 1,
            'per_page' => 1,
        ];

        if ($estrato !== null && $estrato !== '') {
            $params['estrato'] = $estrato;
        }

        $response = $this->listarResumen($params);
        $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

        if (empty($response['success']) || !is_array($payload)) {
            return null;
        }

        $data = (isset($payload['success']) && array_key_exists('data', $payload) && is_array($payload['data']))
            ? $payload['data']
            : $payload;

        if (!isset($data['pagination']) || !is_array($data['pagination'])) {
            return null;
        }

        return isset($data['pagination']['total']) ? (int)$data['pagination']['total'] : null;
    }

    public function ultimas($limit = 5)
    {
        $limit = is_numeric($limit) ? (int)$limit : 5;
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 20) {
            $limit = 20;
        }

        $response = $this->listarResumen([
            'page' => 1,
            'per_page' => $limit,
        ]);

        $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

        if (empty($response['success']) || !is_array($payload)) {
            return [];
        }

        $data = (isset($payload['success']) && array_key_exists('data', $payload) && is_array($payload['data']))
            ? $payload['data']
            : $payload;

        $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

        return $items;
    }
}
