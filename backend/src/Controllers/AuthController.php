<?php

namespace App\Controllers;

use App\Core\TenantContext;
use App\Services\AuthService;

class AuthController
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        header('Content-Type: application/json');

        $rawInput = file_get_contents('php://input');
        $requestData = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($requestData)) {
            $requestData = $_POST;
        }

        // En login NO usamos fallback: solo validamos tenant si el cliente lo envía explícitamente.
        $institutoId = TenantContext::resolveInstitutoId($requestData, false);

        $resultado = $this->authService->login($requestData, $institutoId);

        if ($resultado['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Inicio de sesión exitoso',
            ]);
            return;
        }

        http_response_code($resultado['status'] ?? 401);
        echo json_encode([
            'success' => false,
            'data' => [
                'errors' => $resultado['errors'] ?? [],
            ],
            'message' => $resultado['message'] ?? 'Credenciales inválidas',
        ]);
    }
}
