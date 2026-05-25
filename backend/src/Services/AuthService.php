<?php

namespace App\Services;

use App\Core\AuthToken;
use App\Core\Validator;
use App\Models\UsuarioModel;

class AuthService
{
    private $validator;
    private $usuarioModel;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->usuarioModel = new UsuarioModel();
    }

    public function login(array $requestData, $institutoId = null)
    {
        $requestData = $this->normalizar($requestData);

        $rules = [
            'ci' => 'required',
            'password' => 'required',
        ];

        $errores = $this->validator->validate($requestData, $rules);
        if (!empty($errores)) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Datos inválidos',
                'errors' => $errores,
            ];
        }

        $ci = (string)$requestData['ci'];
        $password = (string)$requestData['password'];

        $usuario = $this->usuarioModel->findByCiWithRoleAndInstituto($ci);
        if (!$usuario) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Credenciales inválidas',
                'errors' => ['auth' => ['CI o contraseña incorrecta.']],
            ];
        }

        if (empty($usuario['activo']) || (int)$usuario['activo'] !== 1) {
            return [
                'success' => false,
                'status' => 403,
                'message' => 'Usuario inactivo',
                'errors' => ['auth' => ['Usuario desactivado.']],
            ];
        }

        if (!password_verify($password, $usuario['password'])) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Credenciales inválidas',
                'errors' => ['auth' => ['CI o contraseña incorrecta.']],
            ];
        }

        // Multi-tenant: si el usuario está amarrado a una sede, validamos contra el tenant enviado (si existe)
        $usuarioInstitutoId = isset($usuario['instituto_id']) ? (int)$usuario['instituto_id'] : null;
        $rolNombre = $usuario['rol_nombre'] ?? null;
        $rolCodigo = $usuario['rol_codigo'] ?? null;

        if ($usuarioInstitutoId) {
            if (!empty($institutoId) && (int)$institutoId !== $usuarioInstitutoId) {
                return [
                    'success' => false,
                    'status' => 403,
                    'message' => 'Acceso denegado para este instituto',
                    'errors' => ['tenant' => ['El usuario no pertenece al instituto indicado.']],
                ];
            }
        } else {
            // SUPER_ADMIN: no requiere instituto
            if ($rolCodigo !== 'SUPER_ADMIN') {
                // Usuario sin instituto pero no es SUPER_ADMIN: inconsistencia
                return [
                    'success' => false,
                    'status' => 403,
                    'message' => 'Usuario sin instituto asignado',
                    'errors' => ['tenant' => ['El usuario no tiene instituto asignado.']],
                ];
            }
        }

        // No retornamos password
        unset($usuario['password']);

        $token = AuthToken::issue([
            'sub' => (int)$usuario['id'],
            'ci' => (string)$usuario['ci'],
            'rol_id' => isset($usuario['rol_id']) ? (int)$usuario['rol_id'] : null,
            'rol' => $rolCodigo,
            'rol_nombre' => $rolNombre,
            'instituto_id' => $usuarioInstitutoId,
        ]);

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => (int)$usuario['id'],
                    'ci' => $usuario['ci'],
                    'nombre_completo' => $usuario['nombre_completo'],
                    'rol' => [
                        'id' => isset($usuario['rol_id']) ? (int)$usuario['rol_id'] : null,
                        'codigo' => $rolCodigo,
                        'nombre' => $rolNombre,
                    ],
                    'instituto' => [
                        'id' => $usuarioInstitutoId,
                        'siglas' => $usuario['instituto_siglas'] ?? null,
                        'nombre' => $usuario['instituto_nombre'] ?? null,
                    ],
                ],
            ],
        ];
    }

    private function normalizar(array $requestData)
    {
        if (isset($requestData['ci'])) {
            $requestData['ci'] = trim((string)$requestData['ci']);
        }
        if (isset($requestData['password'])) {
            $requestData['password'] = (string)$requestData['password'];
        }

        return $requestData;
    }
}
