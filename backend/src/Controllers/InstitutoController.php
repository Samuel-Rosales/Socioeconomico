<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\InstitutoModel;

class InstitutoController
{
    private $model;

    public function __construct()
    {
        $this->model = new InstitutoModel();
    }

    public function estadoEncuesta($params = [])
    {
        header('Content-Type: application/json');

        $map = $this->model->getEstadoEncuestas();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $map,
            'message' => 'Estado de encuestas obtenido correctamente',
        ]);
    }

    public function toggleEncuesta($params = [])
    {
        header('Content-Type: application/json');

        Auth::requireAuth(['SUPER_ADMIN']);

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || (int)$id <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'data' => ['errors' => ['id' => ['ID inválido']]],
                'message' => 'ID de instituto requerido',
            ]);
            return;
        }

        $ok = $this->model->toggleEncuestaActiva((int)$id);
        if (!$ok) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'data' => ['errors' => ['instituto' => ['Instituto no encontrado']]],
                'message' => 'No se pudo alternar el estado',
            ]);
            return;
        }

        $nuevoEstado = $this->model->getEncuestaActivaById((int)$id);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => ['encuesta_activa' => $nuevoEstado],
            'message' => $nuevoEstado ? 'Encuestas activadas' : 'Encuestas desactivadas',
        ]);
    }
}
