<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\UsuarioService;

class AdminUsersController extends Controller
{
    private $usuarios;

    public function __construct()
    {
        $this->usuarios = new UsuarioService();
    }

    private function checkAuth()
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
            $this->flash('error', 'No autorizado: solo SUPER_ADMIN puede gestionar usuarios.');
            $this->redirect(BASE_URL . '/admin');
        }
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

    public function create()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/usuarios');
        }

        $data = [
            'ci' => isset($_POST['ci']) ? trim((string)$_POST['ci']) : '',
            'nombre_completo' => isset($_POST['nombre_completo']) ? trim((string)$_POST['nombre_completo']) : '',
            'password' => isset($_POST['password']) ? (string)$_POST['password'] : '',
            'rol_id' => isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 0,
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        if (isset($_POST['instituto_id']) && $_POST['instituto_id'] !== '') {
            $data['instituto_id'] = is_numeric($_POST['instituto_id']) ? (int)$_POST['instituto_id'] : null;
        }

        try {
            $response = $this->usuarios->crear($data);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (!empty($response['success']) && $payload && !empty($payload['success'])) {
                $this->flash('success', 'Usuario creado correctamente.');
                $this->redirect(BASE_URL . '/admin/usuarios');
            }

            $message = 'No se pudo crear el usuario.';
            $errors = [];
            if (is_array($payload)) {
                if (isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                    $message = $payload['message'];
                }
                if (isset($payload['data']['errors']) && is_array($payload['data']['errors'])) {
                    $errors = $payload['data']['errors'];
                }
            }

            $this->flash('error', $message, $errors);
            $this->redirect(BASE_URL . '/admin/usuarios');
        } catch (\Exception $e) {
            $this->flash('error', 'Error de conexión con el servidor: ' . $e->getMessage());
            $this->redirect(BASE_URL . '/admin/usuarios');
        }
    }

    public function update($id)
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/usuarios');
        }

        $payload = [];

        $ci = isset($_POST['ci']) ? trim((string)$_POST['ci']) : null;
        if ($ci !== null && $ci !== '') {
            $payload['ci'] = $ci;
        }

        $nombre = isset($_POST['nombre_completo']) ? trim((string)$_POST['nombre_completo']) : null;
        if ($nombre !== null && $nombre !== '') {
            $payload['nombre_completo'] = $nombre;
        }

        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        if (trim($password) !== '') {
            $payload['password'] = $password;
        }

        if (isset($_POST['rol_id']) && is_numeric($_POST['rol_id'])) {
            $payload['rol_id'] = (int)$_POST['rol_id'];
        }

        // checkbox
        $payload['activo'] = isset($_POST['activo']) ? 1 : 0;

        if (isset($_POST['instituto_id'])) {
            if ($_POST['instituto_id'] === '') {
                $payload['instituto_id'] = null;
            } elseif (is_numeric($_POST['instituto_id'])) {
                $payload['instituto_id'] = (int)$_POST['instituto_id'];
            }
        }

        try {
            $response = $this->usuarios->actualizar($id, $payload);
            $apiPayload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (!empty($response['success']) && $apiPayload && !empty($apiPayload['success'])) {
                $this->flash('success', 'Usuario actualizado correctamente.');
                $this->redirect(BASE_URL . '/admin/usuarios');
            }

            $message = 'No se pudo actualizar el usuario.';
            $errors = [];
            if (is_array($apiPayload)) {
                if (isset($apiPayload['message']) && is_string($apiPayload['message']) && trim($apiPayload['message']) !== '') {
                    $message = $apiPayload['message'];
                }
                if (isset($apiPayload['data']['errors']) && is_array($apiPayload['data']['errors'])) {
                    $errors = $apiPayload['data']['errors'];
                }
            }

            $this->flash('error', $message, $errors);
            $this->redirect(BASE_URL . '/admin/usuarios');
        } catch (\Exception $e) {
            $this->flash('error', 'Error de conexión con el servidor: ' . $e->getMessage());
            $this->redirect(BASE_URL . '/admin/usuarios');
        }
    }

    public function delete($id)
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/usuarios');
        }

        try {
            $response = $this->usuarios->eliminar($id);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (!empty($response['success']) && $payload && !empty($payload['success'])) {
                $this->flash('success', 'Usuario eliminado correctamente.');
                $this->redirect(BASE_URL . '/admin/usuarios');
            }

            $message = 'No se pudo eliminar el usuario.';
            $errors = [];
            if (is_array($payload)) {
                if (isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                    $message = $payload['message'];
                }
                if (isset($payload['data']['errors']) && is_array($payload['data']['errors'])) {
                    $errors = $payload['data']['errors'];
                }
            }

            $this->flash('error', $message, $errors);
            $this->redirect(BASE_URL . '/admin/usuarios');
        } catch (\Exception $e) {
            $this->flash('error', 'Error de conexión con el servidor: ' . $e->getMessage());
            $this->redirect(BASE_URL . '/admin/usuarios');
        }
    }
}
