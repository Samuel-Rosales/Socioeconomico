<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\TenantContext;
use App\Services\UsuarioService;

class UsuarioController
{
    private $usuarioService;

    public function __construct()
    {
        $this->usuarioService = new UsuarioService();
    }

    public function index($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN']);

        // SUPER_ADMIN: filtra solo si el tenant fue indicado explícitamente.
        // ADMIN_SEDE: el tenant siempre viene del token.
        $institutoId = $actor['rol'] === 'SUPER_ADMIN'
            ? TenantContext::resolveInstitutoId(null, false)
            : ($actor['instituto_id'] ?? null);

        $resultado = $this->usuarioService->listar($institutoId);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $resultado['data'],
            'message' => 'Usuarios listados correctamente',
        ]);
    }

    public function show($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN']);

        $id = $params['id'] ?? null;

        $institutoId = $actor['rol'] === 'SUPER_ADMIN'
            ? TenantContext::resolveInstitutoId(null, false)
            : ($actor['instituto_id'] ?? null);

        $resultado = $this->usuarioService->obtener($id, $institutoId);

        if ($resultado['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Usuario obtenido correctamente',
            ]);
            return;
        }

        http_response_code($resultado['status'] ?? 400);
        echo json_encode([
            'success' => false,
            'data' => ['errors' => $resultado['errors'] ?? []],
            'message' => $resultado['message'] ?? 'Error al obtener el usuario',
        ]);
    }

    public function store($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN']);

        $requestData = $this->getRequestData();

        $tenantInstitutoId = $actor['rol'] === 'SUPER_ADMIN'
            ? TenantContext::resolveInstitutoId($requestData, false)
            : ($actor['instituto_id'] ?? null);

        $resultado = $this->usuarioService->crear($requestData, $tenantInstitutoId, $actor);

        if ($resultado['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Usuario creado correctamente',
            ]);
            return;
        }

        http_response_code($resultado['status'] ?? 400);
        echo json_encode([
            'success' => false,
            'data' => ['errors' => $resultado['errors'] ?? []],
            'message' => $resultado['message'] ?? 'Error al crear el usuario',
        ]);
    }

    public function update($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN']);

        $id = $params['id'] ?? null;
        $requestData = $this->getRequestData();

        // $tenantInstitutoId = $actor['rol'] === 'SUPER_ADMIN'
        //     ? TenantContext::resolveInstitutoId($requestData, false)
        //     : ($actor['instituto_id'] ?? null); 

        // $logEntry = [
        //     'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        //     'context' => 'UsuarioController::update',
        //     'id' => $id,
        //     'tenant_instituto_id' => $tenantInstitutoId ?? 'null',
        //     'actor' => $actor,
        //     'requestData' => $requestData,
        // ];

        // $logLine = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . str_repeat('-', 80) . PHP_EOL;

        // file_put_contents(__DIR__ . '/debug_REQ.log', $logLine, FILE_APPEND | LOCK_EX);

        $resultado = $this->usuarioService->actualizar($id, $requestData, null, $actor);

        if ($resultado['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Usuario actualizado correctamente',
            ]);
            return;
        }

        http_response_code($resultado['status'] ?? 400);
        echo json_encode([
            'success' => false,
            'data' => ['errors' => $resultado['errors'] ?? []],
            'message' => $resultado['message'] ?? 'Error al actualizar el usuario',
        ]);
    }

    public function destroy($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN']);

        $id = $params['id'] ?? null;

        $tenantInstitutoId = $actor['rol'] === 'SUPER_ADMIN'
            ? TenantContext::resolveInstitutoId(null, false)
            : ($actor['instituto_id'] ?? null);

        $resultado = $this->usuarioService->eliminar($id, $tenantInstitutoId, $actor);

        if ($resultado['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Usuario eliminado correctamente',
            ]);
            return;
        }

        http_response_code($resultado['status'] ?? 400);
        echo json_encode([
            'success' => false,
            'data' => ['errors' => $resultado['errors'] ?? []],
            'message' => $resultado['message'] ?? 'Error al eliminar el usuario',
        ]);
    }

    private function getRequestData()
    {
        $rawInput = file_get_contents('php://input');
        $requestData = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($requestData)) {
            $requestData = $_POST;
        }

        return is_array($requestData) ? $requestData : [];
    }
}
