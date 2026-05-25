<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\CatalogoService;

class AdminCatalogsController extends Controller
{
    private $catalogos;

    public function __construct()
    {
        $this->catalogos = new CatalogoService();
    }

    private function flash($type, $message, $errors = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['flash_type'] = (string)$type;
        $_SESSION['flash_message'] = (string)$message;
        if (is_array($errors)) {
            $_SESSION['flash_errors'] = $errors;
        } else {
            unset($_SESSION['flash_errors']);
        }
        $this->closeSession();
    }

    private function checkSuperAdmin()
    {
        if (!$this->hasValidAuthSession()) {
            $this->redirect(BASE_URL . '/login');
        }

        $rol = null;
        if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])
            && isset($_SESSION['auth_user']['rol']) && is_array($_SESSION['auth_user']['rol'])
            && !empty($_SESSION['auth_user']['rol']['codigo'])
        ) {
            $rol = (string)$_SESSION['auth_user']['rol']['codigo'];
        }
        $this->closeSession();

        if ($rol !== 'SUPER_ADMIN') {
            $this->flash('error', 'No autorizado: solo SUPER_ADMIN puede gestionar catálogos.');
            $this->redirect(BASE_URL . '/admin');
        }
    }

    private function redirectBack($resource, $institutoId)
    {
        $qs = [];
        if ($resource !== '') {
            $qs['resource'] = $resource;
        }
        if (!empty($institutoId)) {
            $qs['instituto_id'] = (int)$institutoId;
        }

        $url = BASE_URL . '/admin/catalogos';
        if (!empty($qs)) {
            $url .= '?' . http_build_query($qs);
        }

        $this->redirect($url);
    }

    private function applyCarreraInstitutosState($carreraId, array $desiredInstitutoIds, array $prevInstitutoIds)
    {
        $carreraId = is_numeric($carreraId) ? (int)$carreraId : 0;
        if ($carreraId <= 0) {
            return ['success' => false, 'errors' => ['ID de carrera inválido']];
        }

        $normalize = function (array $arr) {
            $arr = array_values(array_unique(array_filter(array_map(function ($v) {
                return is_numeric($v) ? (int)$v : 0;
            }, $arr), function ($v) {
                return (int)$v > 0;
            })));
            sort($arr);
            return $arr;
        };

        $desired = $normalize($desiredInstitutoIds);
        $prev = $normalize($prevInstitutoIds);

        $toActivate = array_values(array_diff($desired, $prev));
        $toDeactivate = array_values(array_diff($prev, $desired));

        $errors = [];

        foreach ($toActivate as $iid) {
            try {
                $resp = $this->catalogos->adminRestore('carrera', $carreraId, ['instituto_id' => (int)$iid]);
                $payload = isset($resp['data']) && is_array($resp['data']) ? $resp['data'] : null;
                if (empty($resp['success']) || !$payload || empty($payload['success'])) {
                    $msg = 'No se pudo activar en sede #' . (int)$iid;
                    if (is_array($payload) && !empty($payload['message'])) {
                        $msg = (string)$payload['message'];
                    }
                    $errors[] = $msg;
                }
            } catch (\Exception $e) {
                $errors[] = 'Error activando en sede #' . (int)$iid . ': ' . $e->getMessage();
            }
        }

        foreach ($toDeactivate as $iid) {
            try {
                $resp = $this->catalogos->adminDelete('carrera', $carreraId, ['instituto_id' => (int)$iid]);
                $payload = isset($resp['data']) && is_array($resp['data']) ? $resp['data'] : null;
                if (empty($resp['success']) || !$payload || empty($payload['success'])) {
                    $msg = 'No se pudo desactivar en sede #' . (int)$iid;
                    if (is_array($payload) && !empty($payload['message'])) {
                        $msg = (string)$payload['message'];
                    }
                    $errors[] = $msg;
                }
            } catch (\Exception $e) {
                $errors[] = 'Error desactivando en sede #' . (int)$iid . ': ' . $e->getMessage();
            }
        }

        return ['success' => empty($errors), 'errors' => $errors];
    }

    public function create()
    {
        $this->checkSuperAdmin();

        $resource = isset($_POST['resource']) ? trim((string)$_POST['resource']) : '';
        $institutoId = isset($_POST['instituto_id']) && is_numeric($_POST['instituto_id']) ? (int)$_POST['instituto_id'] : null;

        $data = $_POST;
        unset($data['resource'], $data['share_instituto_ids'], $data['instituto_activo_ids'], $data['prev_active_instituto_ids']);

        $desiredInstitutos = [];
        if (isset($_POST['instituto_activo_ids']) && is_array($_POST['instituto_activo_ids'])) {
            $desiredInstitutos = $_POST['instituto_activo_ids'];
        }
        $prevInstitutos = [];
        if (!empty($_POST['prev_active_instituto_ids']) && is_string($_POST['prev_active_instituto_ids'])) {
            $decoded = json_decode($_POST['prev_active_instituto_ids'], true);
            if (is_array($decoded)) {
                $prevInstitutos = $decoded;
            }
        }

        try {
            $response = $this->catalogos->adminCreate($resource, $data);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (!empty($response['success']) && $payload && !empty($payload['success'])) {
                // Carrera: aplicar estado por sedes (Instituto_Carrera)
                if ($resource === 'carrera') {
                    $newId = isset($payload['data']['id']) ? (int)$payload['data']['id'] : 0;
                    $apply = $this->applyCarreraInstitutosState($newId, $desiredInstitutos, $prevInstitutos);
                    if (!empty($apply['errors'])) {
                        $this->flash('error', 'Registro creado, pero con errores al aplicar sedes.', ['sedes' => $apply['errors']]);
                        $this->redirectBack($resource, $institutoId);
                    }
                }

                $this->flash('success', 'Registro creado correctamente.');
            } else {
                $message = 'No se pudo crear el registro.';
                if ($payload && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                    $message = $payload['message'];
                }
                $errors = $payload && isset($payload['data']['errors']) && is_array($payload['data']['errors']) ? $payload['data']['errors'] : null;
                $this->flash('error', $message, $errors);
            }
        } catch (\Exception $e) {
            $this->flash('error', 'Error de conexión con el servidor: ' . $e->getMessage());
        }

        $this->redirectBack($resource, $institutoId);
    }

    public function update($id)
    {
        $this->checkSuperAdmin();

        $id = is_numeric($id) ? (int)$id : 0;
        $resource = isset($_POST['resource']) ? trim((string)$_POST['resource']) : '';
        $institutoId = isset($_POST['instituto_id']) && is_numeric($_POST['instituto_id']) ? (int)$_POST['instituto_id'] : null;

        $data = $_POST;
        unset($data['resource'], $data['share_instituto_ids'], $data['instituto_activo_ids'], $data['prev_active_instituto_ids']);

        $desiredInstitutos = [];
        if (isset($_POST['instituto_activo_ids']) && is_array($_POST['instituto_activo_ids'])) {
            $desiredInstitutos = $_POST['instituto_activo_ids'];
        }
        $prevInstitutos = [];
        if (!empty($_POST['prev_active_instituto_ids']) && is_string($_POST['prev_active_instituto_ids'])) {
            $decoded = json_decode($_POST['prev_active_instituto_ids'], true);
            if (is_array($decoded)) {
                $prevInstitutos = $decoded;
            }
        }

        try {
            $response = $this->catalogos->adminUpdate($resource, $id, $data);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (!empty($response['success']) && $payload && !empty($payload['success'])) {
                // Carrera: aplicar estado por sedes (Instituto_Carrera)
                if ($resource === 'carrera' && $id > 0) {
                    $apply = $this->applyCarreraInstitutosState($id, $desiredInstitutos, $prevInstitutos);
                    if (!empty($apply['errors'])) {
                        $this->flash('error', 'Registro actualizado, pero con errores al aplicar sedes.', ['sedes' => $apply['errors']]);
                        $this->redirectBack($resource, $institutoId);
                    }
                }

                $this->flash('success', 'Registro actualizado correctamente.');
            } else {
                $message = 'No se pudo actualizar el registro.';
                if ($payload && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                    $message = $payload['message'];
                }
                $errors = $payload && isset($payload['data']['errors']) && is_array($payload['data']['errors']) ? $payload['data']['errors'] : null;
                $this->flash('error', $message, $errors);
            }
        } catch (\Exception $e) {
            $this->flash('error', 'Error de conexión con el servidor: ' . $e->getMessage());
        }

        $this->redirectBack($resource, $institutoId);
    }

    public function delete($id)
    {
        $this->checkSuperAdmin();

        $id = is_numeric($id) ? (int)$id : 0;
        $resource = isset($_POST['resource']) ? trim((string)$_POST['resource']) : '';
        $institutoId = isset($_POST['instituto_id']) && is_numeric($_POST['instituto_id']) ? (int)$_POST['instituto_id'] : null;

        try {
            $qs = [];
            if (!empty($institutoId)) {
                $qs['instituto_id'] = (int)$institutoId;
            }

            $response = $this->catalogos->adminDelete($resource, $id, $qs);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (!empty($response['success']) && $payload && !empty($payload['success'])) {
                $this->flash('success', 'Registro desactivado correctamente.');
            } else {
                $message = 'No se pudo desactivar el registro.';
                if ($payload && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                    $message = $payload['message'];
                }
                $errors = $payload && isset($payload['data']['errors']) && is_array($payload['data']['errors']) ? $payload['data']['errors'] : null;
                $this->flash('error', $message, $errors);
            }
        } catch (\Exception $e) {
            $this->flash('error', 'Error de conexión con el servidor: ' . $e->getMessage());
        }

        $this->redirectBack($resource, $institutoId);
    }

    public function restore($id)
    {
        $this->checkSuperAdmin();

        $id = is_numeric($id) ? (int)$id : 0;
        $resource = isset($_POST['resource']) ? trim((string)$_POST['resource']) : '';
        $institutoId = isset($_POST['instituto_id']) && is_numeric($_POST['instituto_id']) ? (int)$_POST['instituto_id'] : null;

        try {
            $data = [];
            if (!empty($institutoId)) {
                $data['instituto_id'] = (int)$institutoId;
            }

            $response = $this->catalogos->adminRestore($resource, $id, $data);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (!empty($response['success']) && $payload && !empty($payload['success'])) {
                $this->flash('success', 'Registro restaurado correctamente.');
            } else {
                $message = 'No se pudo restaurar el registro.';
                if ($payload && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                    $message = $payload['message'];
                }
                $errors = $payload && isset($payload['data']['errors']) && is_array($payload['data']['errors']) ? $payload['data']['errors'] : null;
                $this->flash('error', $message, $errors);
            }
        } catch (\Exception $e) {
            $this->flash('error', 'Error de conexión con el servidor: ' . $e->getMessage());
        }

        $this->redirectBack($resource, $institutoId);
    }
}
