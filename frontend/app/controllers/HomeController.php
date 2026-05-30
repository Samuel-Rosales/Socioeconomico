<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\ApiService;

/**
 * HomeController - Controlador para la página principal (selección de sede)
 */
class HomeController extends Controller
{
    /**
     * Muestra la página de inicio con la selección de sede
     */
    public function index()
    {
        $sedes = [
            [
                'id' => 'IUJO-BARQUISIMETO',
                'nombre' => 'IUJO Barquisimeto',
                'logo' => 'iujo.png',
                'sigla_db' => 'IUJO-BARQUISIMETO'
            ],
            [
                'id' => 'IUJO-PETARE',
                'nombre' => 'IUJO Petare',
                'logo' => 'iujo.png',
                'sigla_db' => 'IUJO-PETARE'
            ],
            [
                'id' => 'IUJO-CATIA',
                'nombre' => 'IUJO Catia',
                'logo' => 'iujo.png',
                'sigla_db' => 'IUJO-CARACAS'
            ],
            [
                'id' => 'IUJO-GUANARITO',
                'nombre' => 'IUJO Guanarito',
                'logo' => 'iujo.png',
                'sigla_db' => 'IUJO-GUANARITO'
            ],
            [
                'id' => 'IUSF',
                'nombre' => 'IUSF',
                'logo' => 'iujo.png',
                'sigla_db' => 'IUSF'
            ]
        ];

        $estadoEncuestas = $this->obtenerEstadoEncuestas();

        $this->view('home/index', [
            'sedes' => $sedes,
            'estadoEncuestas' => $estadoEncuestas,
        ]);
    }

    private function obtenerEstadoEncuestas()
    {
        try {
            $api = new ApiService();
            $response = $api->get('/instituto/estado-encuesta');
            if ($response['success'] && isset($response['data']['data'])) {
                return $response['data']['data'];
            }
        } catch (\Exception $e) {
        }
        return [];
    }
}
