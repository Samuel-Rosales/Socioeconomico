<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Services\CatalogService;
use App\Core\TenantContext;

class CatalogController
{
    private $catalogService;

    public function __construct()
    {
        // En PHP 7.1 declaramos y asignamos manualmente
        $this->catalogService = new CatalogService();
    }

    public function index($params)
    {
        $resource = $params['resource'];

        // Multi-tenant: algunos catálogos dependen del Instituto (tenant)
        $institutoId = TenantContext::resolveInstitutoId();

        // El controlador le delega la responsabilidad al servicio
        $data = $this->catalogService->getCatalogData($resource, $institutoId);

        header('Content-Type: application/json');

        if ($data === null) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'data' => [
                    'errors' => [
                        'catalogo' => ["El catálogo '$resource' no existe en el sistema."],
                    ],
                ],
                'message' => 'Catálogo no encontrado',
            ]);
            return;
        }

        if (is_array($data) && isset($data['error'])) {
            http_response_code($data['status'] ?? 400);
            echo json_encode([
                'success' => false,
                'data' => [
                    'errors' => [
                        'catalogo' => [$data['error']],
                    ],
                ],
                'message' => 'Error al obtener el catálogo',
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'message' => 'Catálogo obtenido correctamente',
        ]);
    }

    /**
     * GET /catalogo
     * Lista catálogos disponibles (para construir el menú en el frontend)
     */
    public function resources($params = [])
    {
        // Solo SUPER_ADMIN puede ver el menú administrativo de catálogos.
        // Nota: /catalogo/:resource se mantiene público para el formulario.
        Auth::requireAuth(['SUPER_ADMIN']);

        $institutoId = TenantContext::resolveInstitutoId();
        $items = $this->catalogService->listCatalogs($institutoId);

        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $items,
            'message' => 'Catálogos listados correctamente',
        ]);
    }

    public function adminIndex($params = [])
    {
        header('Content-Type: application/json');
        Auth::requireAuth(['SUPER_ADMIN']);

        $resource = $params['resource'] ?? null;
        $institutoId = TenantContext::resolveInstitutoId();

        $resultado = $this->catalogService->getAdminCatalogItems($resource, $institutoId);
        if ($resultado === null) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'data' => ['errors' => ['catalogo' => ["El catálogo '$resource' no existe en el sistema."]]],
                'message' => 'Catálogo no encontrado',
            ]);
            return;
        }

        if (is_array($resultado) && isset($resultado['error'])) {
            http_response_code($resultado['status'] ?? 400);
            echo json_encode([
                'success' => false,
                'data' => ['errors' => ['catalogo' => [$resultado['error']]]],
                'message' => 'Error al obtener el catálogo',
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $resultado,
            'message' => 'Catálogo obtenido correctamente',
        ]);
    }

    public function adminStore($params = [])
    {
        header('Content-Type: application/json');
        Auth::requireAuth(['SUPER_ADMIN']);

        $resource = $params['resource'] ?? null;
        $data = $this->getRequestData();
        $institutoId = TenantContext::resolveInstitutoId($data);

        $resultado = $this->catalogService->adminCreate($resource, $data, $institutoId);
        if (!is_array($resultado) || empty($resultado['success'])) {
            http_response_code(isset($resultado['status']) ? (int)$resultado['status'] : 400);
            echo json_encode([
                'success' => false,
                'data' => ['errors' => $resultado['errors'] ?? []],
                'message' => $resultado['message'] ?? 'Error al crear el registro',
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $resultado['data'] ?? null,
            'message' => 'Registro creado correctamente',
        ]);
    }

    public function adminUpdate($params = [])
    {
        header('Content-Type: application/json');
        Auth::requireAuth(['SUPER_ADMIN']);

        $resource = $params['resource'] ?? null;
        $id = $params['id'] ?? null;
        $data = $this->getRequestData();
        $institutoId = TenantContext::resolveInstitutoId($data);

        $resultado = $this->catalogService->adminUpdate($resource, $id, $data, $institutoId);
        if (!is_array($resultado) || empty($resultado['success'])) {
            http_response_code(isset($resultado['status']) ? (int)$resultado['status'] : 400);
            echo json_encode([
                'success' => false,
                'data' => ['errors' => $resultado['errors'] ?? []],
                'message' => $resultado['message'] ?? 'Error al actualizar el registro',
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $resultado['data'] ?? null,
            'message' => 'Registro actualizado correctamente',
        ]);
    }

    public function adminDestroy($params = [])
    {
        header('Content-Type: application/json');
        Auth::requireAuth(['SUPER_ADMIN']);

        $resource = $params['resource'] ?? null;
        $id = $params['id'] ?? null;
        $institutoId = TenantContext::resolveInstitutoId();

        $resultado = $this->catalogService->adminDelete($resource, $id, $institutoId);
        if (!is_array($resultado) || empty($resultado['success'])) {
            http_response_code(isset($resultado['status']) ? (int)$resultado['status'] : 400);
            echo json_encode([
                'success' => false,
                'data' => ['errors' => $resultado['errors'] ?? []],
                'message' => $resultado['message'] ?? 'Error al eliminar el registro',
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $resultado['data'] ?? null,
            'message' => 'Registro desactivado correctamente',
        ]);
    }

    public function adminRestore($params = [])
    {
        header('Content-Type: application/json');
        Auth::requireAuth(['SUPER_ADMIN']);

        $resource = $params['resource'] ?? null;
        $id = $params['id'] ?? null;
        $data = $this->getRequestData();
        $institutoId = TenantContext::resolveInstitutoId($data);

        $resultado = $this->catalogService->adminRestore($resource, $id, $institutoId);
        if (!is_array($resultado) || empty($resultado['success'])) {
            http_response_code(isset($resultado['status']) ? (int)$resultado['status'] : 400);
            echo json_encode([
                'success' => false,
                'data' => ['errors' => $resultado['errors'] ?? []],
                'message' => $resultado['message'] ?? 'Error al restaurar el registro',
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $resultado['data'] ?? null,
            'message' => 'Registro restaurado correctamente',
        ]);
    }

    /**
     * GET /catalogo-admin/carrera/activos
     * Devuelve un mapa carrera_id => [instituto_id...] donde la carrera está activa (Instituto_Carrera.activo=1)
     */
    public function adminCarreraActivos($params = [])
    {
        header('Content-Type: application/json');
        Auth::requireAuth(['SUPER_ADMIN']);

        $map = $this->catalogService->getCarreraActivosPorInstituto();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $map,
            'message' => 'Mapa de carreras activas obtenido correctamente',
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
