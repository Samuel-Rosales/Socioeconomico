<?php

namespace App\Controllers;

use Core\Controller;
use App\Services\ApiService;

/**
 * AuthController - Controlador para la autenticación
 */
class AuthController extends Controller
{
    private $apiService;

    public function __construct()
    {
        $this->apiService = new ApiService();
    }

    /**
     * Muestra la vista de login
     */
    public function login()
    {
        if ($this->isAuthenticated()) {
            $this->redirect(BASE_URL . '/admin');
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : null;
        $oldUser = isset($_SESSION['login_user']) ? $_SESSION['login_user'] : '';

        unset($_SESSION['login_error']);
        unset($_SESSION['login_user']);
        $this->closeSession();

        $this->view('auth/login', [
            'error' => $error,
            'oldUser' => $oldUser,
            'title' => 'Iniciar sesión'
        ]);
    }

    /**
     * Procesa la autenticación del usuario
     */
    public function authenticate()
    {
        if (!$this->isPost()) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuario = trim((string) $this->post('usuario', ''));
        $contrasena = trim((string) $this->post('contrasena', ''));

        if ($usuario === '' || $contrasena === '') {
            $_SESSION['login_error'] = 'Debes ingresar usuario y contraseña.';
            $_SESSION['login_user'] = $usuario;
            $this->closeSession();
            $this->redirect(BASE_URL . '/login');
            return;
        }

        try {
            $response = $this->apiService->post('/login', [
                'ci' => $usuario,
                'password' => $contrasena,
            ]);

            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if ($response['success'] && $payload && !empty($payload['success'])) {
                // Seguridad: mitigar session fixation
                if (function_exists('session_regenerate_id')) {
                    session_regenerate_id(true);
                }

                // Guardamos token + usuario retornado por el backend
                if (isset($payload['data']) && is_array($payload['data'])) {
                    if (!empty($payload['data']['token'])) {
                        $_SESSION['auth_token'] = (string) $payload['data']['token'];
                    }

                    if (isset($payload['data']['user']) && is_array($payload['data']['user'])) {
                        $_SESSION['auth_user'] = $payload['data']['user'];
                    } else {
                        $_SESSION['auth_user'] = ['ci' => $usuario];
                    }
                } else {
                    $_SESSION['auth_user'] = ['ci' => $usuario];
                }

                $_SESSION['last_activity'] = time();
                $this->closeSession();

                $this->redirect(BASE_URL . '/admin');
                return;
            }

            $message = 'Credenciales inválidas.';
            if ($payload && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                $message = $payload['message'];
            }

            $_SESSION['login_error'] = $message;
            $_SESSION['login_user'] = $usuario;
            $this->closeSession();
            $this->redirect(BASE_URL . '/login');
            return;
        } catch (\Exception $e) {
            $_SESSION['login_error'] = 'Error de conexión con el servidor: ' . $e->getMessage();
            $_SESSION['login_user'] = $usuario;
            $this->closeSession();
            $this->redirect(BASE_URL . '/login');
            return;
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['auth_user']);
        unset($_SESSION['auth_token']);
        unset($_SESSION['last_activity']);
        if (function_exists('session_regenerate_id')) {
            session_regenerate_id(true);
        }
        $this->closeSession();
        $this->redirect(BASE_URL . '/login');
    }

    /**
     * Invalida el token actual sin cerrar sesión local
     * Usado cuando la sesión expira por inactividad
     */
    public function invalidate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $_SESSION['auth_token'] ?? null;
        
        if ($token) {
            try {
                $this->apiService->post('/auth/logout', ['token' => $token]);
            } catch (\Exception $e) {
            }
        }

        unset($_SESSION['auth_user']);
        unset($_SESSION['auth_token']);
        unset($_SESSION['last_activity']);

        $_SESSION['login_error'] = 'Tu sesión expiró por inactividad. Inicia sesión nuevamente.';
        $this->closeSession();

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Verifica si el usuario está autenticado
     */
    private function isAuthenticated()
    {
        return $this->hasValidAuthSession();
    }
}
