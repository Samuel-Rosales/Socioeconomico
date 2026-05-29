<?php

namespace App\Core;

use App\Models\UsuarioModel;

class Auth
{
    public static function requireAuth(?array $allowedRoles = null)
    {
        $token = self::getBearerToken();
        if (empty($token)) {
            self::fail(401, 'No autenticado', ['auth' => ['Falta header Authorization: Bearer <token>.']]);
        }

        $payload = AuthToken::verify($token);
        if (!$payload) {
            self::fail(401, 'Token inválido o expirado', ['auth' => ['Token inválido o expirado.']]);
        }

        // Validación adicional: el usuario sigue activo en BD
        $userId = isset($payload['sub']) ? (int)$payload['sub'] : 0;
        if ($userId <= 0) {
            self::fail(401, 'Token inválido', ['auth' => ['Token sin subject (sub).']]);
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->getByIdRaw($userId);

        if (!$usuario || (int)($usuario['activo'] ?? 0) !== 1) {
            self::fail(401, 'Usuario inválido o inactivo', ['auth' => ['El usuario no existe o está inactivo.']]);
        }

        $rolCodigo = $payload['rol'] ?? ($usuario['rol_codigo'] ?? null);
        $rolNombre = $payload['rol_nombre'] ?? ($usuario['rol_nombre'] ?? null);

        if (is_array($allowedRoles) && !empty($allowedRoles) && (!is_string($rolCodigo) || !in_array($rolCodigo, $allowedRoles, true))) {
            self::fail(403, 'No autorizado', ['auth' => ['No tienes permisos para esta acción.']]);
        }

        // Normalizamos lo que retornamos como contexto de auth
        return [
            'id' => $userId,
            'ci' => $payload['ci'] ?? ($usuario['ci'] ?? null),
            'rol' => $rolCodigo,
            'rol_nombre' => $rolNombre,
            'rol_id' => isset($payload['rol_id']) ? (int)$payload['rol_id'] : (isset($usuario['rol_id']) ? (int)$usuario['rol_id'] : null),
            'instituto_id' => array_key_exists('instituto_id', $payload)
                ? ($payload['instituto_id'] !== null ? (int)$payload['instituto_id'] : null)
                : (isset($usuario['instituto_id']) ? (int)$usuario['instituto_id'] : null),
        ];
    }

    public static function getBearerToken()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$authHeader) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        }

        if (!$authHeader && function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                foreach ($headers as $key => $value) {
                    if (strtolower($key) === 'authorization') {
                        $authHeader = $value;
                        break;
                    }
                }
            }
        }

        if (!is_string($authHeader) || trim($authHeader) === '') {
            return null;
        }

        if (stripos($authHeader, 'Bearer ') !== 0) {
            return null;
        }

        return trim(substr($authHeader, 7));
    }

    public static function getActorIfAuthenticated()
    {
        $token = self::getBearerToken();
        if (empty($token)) {
            return null;
        }

        $payload = AuthToken::verify($token);
        if (!$payload) {
            return null;
        }

        $userId = isset($payload['sub']) ? (int)$payload['sub'] : 0;
        if ($userId <= 0) {
            return null;
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->getByIdRaw($userId);
        if (!$usuario || (int)($usuario['activo'] ?? 0) !== 1) {
            return null;
        }

        $rolCodigo = $payload['rol'] ?? ($usuario['rol_codigo'] ?? null);
        $rolNombre = $payload['rol_nombre'] ?? ($usuario['rol_nombre'] ?? null);

        return [
            'id' => $userId,
            'ci' => $payload['ci'] ?? ($usuario['ci'] ?? null),
            'rol' => $rolCodigo,
            'rol_nombre' => $rolNombre,
            'rol_id' => isset($payload['rol_id']) ? (int)$payload['rol_id'] : (isset($usuario['rol_id']) ? (int)$usuario['rol_id'] : null),
            'instituto_id' => array_key_exists('instituto_id', $payload)
                ? ($payload['instituto_id'] !== null ? (int)$payload['instituto_id'] : null)
                : (isset($usuario['instituto_id']) ? (int)$usuario['instituto_id'] : null),
        ];
    }

    private static function fail($status, $message, array $errors = [])
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode([
            'success' => false,
            'data' => [
                'errors' => $errors,
            ],
            'message' => $message,
        ]);
        exit;
    }
}
