<?php

namespace App\Services;

use App\Core\Validator;
use App\Models\RolModel;
use App\Models\UsuarioModel;
use PDOException;

class UsuarioService
{
    private $validator;
    private $usuarioModel;
    private $rolModel;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->usuarioModel = new UsuarioModel();
        $this->rolModel = new RolModel();
    }

    public function listar($institutoId = null)
    {
        $items = !empty($institutoId)
            ? $this->usuarioModel->getAllByInstituto((int)$institutoId)
            : $this->usuarioModel->getAll();

        $items = $this->formatListadoItems($items);

        return [
            'success' => true,
            'data' => [
                'items' => $items,
            ],
        ];
    }

    public function obtener($id, $institutoId = null)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'ID inválido',
                'errors' => ['id' => ['El id debe ser numérico.']],
            ];
        }

        $id = (int)$id;

        $usuario = !empty($institutoId)
            ? $this->usuarioModel->getByIdAndInstituto($id, (int)$institutoId)
            : $this->usuarioModel->getById($id);

        if (!$usuario) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Usuario no encontrado',
                'errors' => ['usuario' => ['No existe o está inactivo.']],
            ];
        }

        return [
            'success' => true,
            'data' => $usuario,
        ];
    }

    public function crear(array $requestData, $tenantInstitutoId = null, $actor = null)
    {
        $actorRol = is_array($actor) ? ($actor['rol'] ?? null) : null;

        $requestData = $this->normalizar($requestData);

        $rules = [
            'ci' => 'required',
            'nombre_completo' => 'required',
            'password' => 'required',
            'rol_id' => 'required|numeric',
            'instituto_id' => 'numeric',
            'activo' => 'numeric',
        ];

        $errores = $this->validator->validate($requestData, $rules);

        $rolId = isset($requestData['rol_id']) ? (int)$requestData['rol_id'] : 0;
        $rol = $rolId > 0 ? $this->rolModel->getById($rolId) : null;
        if (!$rol) {
            $errores['rol_id'][] = 'El rol_id no existe.';
        }

        $rolCodigo = $rol['codigo'] ?? null;

        $institutoId = null;
        if (isset($requestData['instituto_id']) && is_numeric($requestData['instituto_id']) && (int)$requestData['instituto_id'] > 0) {
            $institutoId = (int)$requestData['instituto_id'];
        } elseif (!empty($tenantInstitutoId)) {
            $institutoId = (int)$tenantInstitutoId;
        }

        if ($rolCodigo === 'SUPER_ADMIN') {
            if (!empty($actorRol) && $actorRol !== 'SUPER_ADMIN') {
                $errores['rol_id'][] = 'No tienes permisos para asignar el rol SUPER_ADMIN.';
            }
            $institutoId = null;
        } else {
            if (empty($institutoId)) {
                $errores['instituto_id'][] = 'El instituto_id es obligatorio para este rol.';
            }

            if (!empty($tenantInstitutoId) && !empty($institutoId) && (int)$tenantInstitutoId !== (int)$institutoId) {
                $errores['instituto_id'][] = 'No puedes crear usuarios para otro instituto (tenant).';
            }
        }

        if (!empty($errores)) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Datos inválidos',
                'errors' => $errores,
            ];
        }

        try {
            $passwordHash = password_hash((string)$requestData['password'], PASSWORD_BCRYPT);

            $id = $this->usuarioModel->create([
                'ci' => (string)$requestData['ci'],
                'nombre_completo' => (string)$requestData['nombre_completo'],
                'password' => $passwordHash,
                'rol_id' => $rolId,
                'instituto_id' => $institutoId,
                'activo' => isset($requestData['activo']) && $requestData['activo'] !== null ? (int)$requestData['activo'] : 1,
            ]);

            if (empty($id)) {
                return [
                    'success' => false,
                    'status' => 500,
                    'message' => 'No se pudo crear el usuario',
                    'errors' => ['database' => ['No se pudo obtener el ID del usuario creado.']],
                ];
            }

            $usuario = $this->usuarioModel->getByIdRaw($id);

            return [
                'success' => true,
                'data' => $usuario,
            ];
        } catch (PDOException $e) {
            $message = $this->mapearErrorPDO($e);
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Error al crear el usuario',
                'errors' => ['database' => [$message]],
            ];
        }
    }

    public function actualizar($id, array $requestData, $tenantInstitutoId = null, $actor = null)
    {
        // $logEntry = [
        //     'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        //     'context' => 'UsuarioService::actualizar',
        //     'id' => $id,
        //     'tenant_instituto_id' => $tenantInstitutoId,
        //     'actor' => $actor,
        //     'requestData' => $requestData,
        // ];

        // $logLine = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . str_repeat('-', 80) . PHP_EOL;

        // file_put_contents(__DIR__ . '/debug_REQ.log', $logLine, FILE_APPEND | LOCK_EX);

        $actorRol = is_array($actor) ? ($actor['rol'] ?? null) : null;

        if (!is_numeric($id) || (int)$id <= 0) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'ID inválido',
                'errors' => ['id' => ['El id debe ser numérico.']],
            ];
        }

        $id = (int)$id;
        $requestData = $this->normalizar($requestData);

        $actual = !empty($tenantInstitutoId)
            ? $this->usuarioModel->getByIdAndInstituto($id, (int)$tenantInstitutoId)
            : $this->usuarioModel->getById($id);

        if (!$actual) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Usuario no encontrado',
                'errors' => ['usuario' => ['No existe o está inactivo.']],
            ];
        }

        $rules = [
            'rol_id' => 'numeric',
            'instituto_id' => 'numeric',
            'activo' => 'numeric',
        ];

        $errores = $this->validator->validate($requestData, $rules);

        if (array_key_exists('ci', $requestData) && trim((string)$requestData['ci']) === '') {
            $errores['ci'][] = 'El campo ci no puede estar vacío.';
        }
        if (array_key_exists('nombre_completo', $requestData) && trim((string)$requestData['nombre_completo']) === '') {
            $errores['nombre_completo'][] = 'El campo nombre_completo no puede estar vacío.';
        }
        if (array_key_exists('password', $requestData) && trim((string)$requestData['password']) === '') {
            $errores['password'][] = 'El campo password no puede estar vacío.';
        }

        $rolId = isset($requestData['rol_id']) ? (int)$requestData['rol_id'] : (int)($actual['rol_id'] ?? 0);
        $rolCodigo = $actual['rol_codigo'] ?? null;

        if (isset($requestData['rol_id'])) {
            $rol = $rolId > 0 ? $this->rolModel->getById($rolId) : null;
            if (!$rol) {
                $errores['rol_id'][] = 'El rol_id no existe.';
            } else {
                $rolCodigo = $rol['codigo'] ?? null;
            }
        }

        $institutoIdActual = isset($actual['instituto_id']) && $actual['instituto_id'] !== null ? (int)$actual['instituto_id'] : null;
        $institutoId = $institutoIdActual;

        if (array_key_exists('instituto_id', $requestData)) {
            if (is_numeric($requestData['instituto_id']) && (int)$requestData['instituto_id'] > 0) {
                $institutoId = (int)$requestData['instituto_id'];
            } else {
                $institutoId = null;
            }
        }

        if ($rolCodigo === 'SUPER_ADMIN') {
            if (!empty($actorRol) && $actorRol !== 'SUPER_ADMIN') {
                $errores['rol_id'][] = 'No tienes permisos para asignar el rol SUPER_ADMIN.';
            }
            $institutoId = null;
        } else {
            if (empty($institutoId)) {
                $errores['instituto_id'][] = 'El instituto_id es obligatorio para este rol.';
            }

            if (!empty($tenantInstitutoId) && !empty($institutoId) && (int)$tenantInstitutoId !== (int)$institutoId) {
                $errores['instituto_id'][] = 'No puedes mover usuarios a otro instituto (tenant).';
            }
        }

        if (!empty($errores)) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Datos inválidos',
                'errors' => $errores,
            ];
        }

        $updateData = [];
        if (array_key_exists('ci', $requestData)) {
            $updateData['ci'] = (string)$requestData['ci'];
        }
        if (array_key_exists('nombre_completo', $requestData)) {
            $updateData['nombre_completo'] = (string)$requestData['nombre_completo'];
        }
        if (array_key_exists('rol_id', $requestData)) {
            $updateData['rol_id'] = $rolId;
        }
        if (array_key_exists('activo', $requestData) && $requestData['activo'] !== null) {
            $updateData['activo'] = (int)$requestData['activo'];
        }

        if (array_key_exists('password', $requestData)) {
            $updateData['password'] = password_hash((string)$requestData['password'], PASSWORD_BCRYPT);
        }

        // Solo actualizamos instituto_id si el request lo envía, o si cambió el rol (p.ej. SUPER_ADMIN)
        if (array_key_exists('instituto_id', $requestData) || array_key_exists('rol_id', $requestData)) {
            $updateData['instituto_id'] = $institutoId;
        }

        if (empty($updateData)) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Nada para actualizar',
                'errors' => ['update' => ['No se enviaron campos válidos para actualizar.']],
            ];
        }

        try {
            $ok = $this->usuarioModel->updateById($id, $updateData);
            if (!$ok) {
                return [
                    'success' => false,
                    'status' => 500,
                    'message' => 'No se pudo actualizar el usuario',
                    'errors' => ['database' => ['La actualización no fue aplicada.']],
                ];
            }

            $usuario = $this->usuarioModel->getByIdRaw($id);

            return [
                'success' => true,
                'data' => $usuario,
            ];
        } catch (PDOException $e) {
            $message = $this->mapearErrorPDO($e);
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Error al actualizar el usuario',
                'errors' => ['database' => [$message]],
            ];
        }
    }

    public function eliminar($id, $tenantInstitutoId = null, $actor = null)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'ID inválido',
                'errors' => ['id' => ['El id debe ser numérico.']],
            ];
        }

        $id = (int)$id;

        $usuario = !empty($tenantInstitutoId)
            ? $this->usuarioModel->getByIdAndInstituto($id, (int)$tenantInstitutoId)
            : $this->usuarioModel->getById($id);

        if (!$usuario) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Usuario no encontrado',
                'errors' => ['usuario' => ['No existe o está inactivo.']],
            ];
        }

        $actorRol = is_array($actor) ? ($actor['rol'] ?? null) : null;
        if (!empty($actorRol) && $actorRol !== 'SUPER_ADMIN' && ($usuario['rol_codigo'] ?? null) === 'SUPER_ADMIN') {
            return [
                'success' => false,
                'status' => 403,
                'message' => 'No autorizado',
                'errors' => ['rol' => ['No tienes permisos para eliminar usuarios SUPER_ADMIN.']],
            ];
        }

        try {
            $ok = $this->usuarioModel->delete($id);
            if (!$ok) {
                return [
                    'success' => false,
                    'status' => 500,
                    'message' => 'No se pudo eliminar el usuario',
                    'errors' => ['database' => ['La eliminación no fue aplicada.']],
                ];
            }

            return [
                'success' => true,
                'data' => ['id' => $id],
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Error al eliminar el usuario',
                'errors' => ['database' => [$e->getMessage()]],
            ];
        }
    }

    private function normalizar(array $requestData)
    {
        if (isset($requestData['ci'])) {
            $requestData['ci'] = trim((string)$requestData['ci']);
        }
        if (isset($requestData['nombre_completo'])) {
            $requestData['nombre_completo'] = trim((string)$requestData['nombre_completo']);
        }
        if (isset($requestData['password'])) {
            $requestData['password'] = (string)$requestData['password'];
        }

        if (array_key_exists('instituto_id', $requestData) && is_string($requestData['instituto_id']) && trim($requestData['instituto_id']) === '') {
            $requestData['instituto_id'] = null;
        }

        if (array_key_exists('activo', $requestData) && is_string($requestData['activo']) && trim($requestData['activo']) === '') {
            $requestData['activo'] = null;
        }

        return $requestData;
    }

    private function mapearErrorPDO(PDOException $e)
    {
        // 23000: violación de integridad (p.ej. UNIQUE ci)
        $sqlState = $e->getCode();
        $msg = $e->getMessage();

        if ($sqlState === '23000') {
            if (stripos($msg, 'Usuario.ci') !== false || stripos($msg, 'ci') !== false) {
                return 'La cédula (ci) ya está registrada.';
            }
            return 'Violación de integridad (dato duplicado o FK inválida).';
        }

        return $msg;
    }

    private function formatListadoItems($items)
    {
        if (!is_array($items)) {
            return [];
        }

        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($item['creado_at']) && is_string($item['creado_at']) && trim($item['creado_at']) !== '') {
                $items[$idx]['creado_at_raw'] = $item['creado_at'];
                $items[$idx]['creado_at'] = $this->formatFechaBonita($item['creado_at']);
            }
        }

        return $items;
    }

    private function formatFechaBonita($value)
    {
        if (!is_string($value) || trim($value) === '') {
            return $value;
        }

        try {
            $dt = new \DateTime($value);
            $meses = [
                1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun',
                7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic',
            ];

            $mes = isset($meses[(int)$dt->format('n')]) ? $meses[(int)$dt->format('n')] : $dt->format('m');
            return $dt->format('d') . ' ' . $mes . ' ' . $dt->format('Y, h:i A');
        } catch (\Exception $e) {
            return $value;
        }
    }
}
