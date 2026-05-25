<?php

namespace App\Controllers;

use Core\Controller;

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
                'id' => 'IUJO-CATIA',
                'nombre' => 'IUJO Catia',
                'logo' => 'iujo.png'
            ],
            [
                'id' => 'IUJO-PETARE',
                'nombre' => 'IUJO Petare',
                'logo' => 'iujo.png'
            ],
            [
                'id' => 'IUJO-GUANARITO',
                'nombre' => 'IUJO Guanarito',
                'logo' => 'iujo.png'
            ],
            [
                'id' => 'IUSF',
                'nombre' => 'IUSF',
                'logo' => 'iujo.png' // Si IUSF tiene otro logo se podrá ajustar después
            ],
            [
                'id' => 'IUJO-BARQUISIMETO',
                'nombre' => 'IUJO Barquisimeto',
                'logo' => 'iujo.png'
            ]
        ];

        $this->view('home/index', ['sedes' => $sedes]);
    }
}
