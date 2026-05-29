<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Services\EncuestaService;
use App\Core\TenantContext;
use App\Core\Env;

class EncuestaController
{
    private $encuestaService;
    private $uploadDir;
    private $uploadPublicBase;

    public function __construct()
    {
        $this->encuestaService = new EncuestaService();

        $defaultUploadDir = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'cedulas';

        $this->uploadDir = (string) (Env::get('UPLOAD_CEDULAS_DIR') ?: $defaultUploadDir);

        // error_log("[ENCUESTA DEBUG] uploadDir: " . $defaultUploadDir);

        $uploadPublicBase = (string) Env::get('UPLOAD_CEDULAS_PUBLIC_BASE');
        $uploadPublicBase = '/' . trim(str_replace('\\', '/', $uploadPublicBase), '/');
        $this->uploadPublicBase = $uploadPublicBase;
    }

    public function index($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN', 'ADMIN_SEDE', 'ANALISTA']);

        // SUPER_ADMIN: filtra solo si el tenant fue indicado explícitamente.
        // Otros roles: el tenant viene del token.
        $institutoId = $actor['rol'] === 'SUPER_ADMIN'
            ? TenantContext::resolveInstitutoId(null, false)
            : ($actor['instituto_id'] ?? null);

        $options = [];
        
        if (isset($_GET['q'])) {
            $options['q'] = (string)$_GET['q'];
        }
        if (isset($_GET['carrera_id'])) {
            $options['carrera_id'] = $_GET['carrera_id'];
        }
        if (isset($_GET['estrato'])) {
            $options['estrato'] = (string)$_GET['estrato'];
        }
        // Compatibilidad: UI antigua enviaba "estado" (completa|pendiente)
        if (!isset($options['estrato']) && isset($_GET['estado'])) {
            $options['estrato'] = (string)$_GET['estado'];
        }
        if (isset($_GET['page'])) {
            $options['page'] = $_GET['page'];
        }
        if (isset($_GET['per_page'])) {
            $options['per_page'] = $_GET['per_page'];
        }

        $resultado = $this->encuestaService->listarResumen($institutoId, $options);

        if ($resultado['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Encuestas listadas correctamente',
            ]);
            return;
        }

        http_response_code($resultado['status'] ?? 400);
        echo json_encode([
            'success' => false,
            'data' => [
                'errors' => $resultado['errors'] ?? [],
            ],
            'message' => $resultado['message'] ?? 'Error al listar encuestas',
        ]);
    }

    public function show($params = [])
    {
        header('Content-Type: application/json');

        $actor = Auth::requireAuth(['SUPER_ADMIN', 'ADMIN_SEDE', 'ANALISTA']);

        $id = $params['id'] ?? null;

        // SUPER_ADMIN: filtra solo si el tenant fue indicado explícitamente.
        // Otros roles: el tenant viene del token.
        $institutoId = $actor['rol'] === 'SUPER_ADMIN'
            ? TenantContext::resolveInstitutoId(null, false)
            : ($actor['instituto_id'] ?? null);

        $resultado = $this->encuestaService->obtenerDetalle($id, $institutoId);

        if ($resultado['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Encuesta obtenida correctamente',
            ]);
            return;
        }

        http_response_code($resultado['status'] ?? 400);
        echo json_encode([
            'success' => false,
            'data' => ['errors' => $resultado['errors'] ?? []],
            'message' => $resultado['message'] ?? 'Error al obtener la encuesta',
        ]);
    }

    public function update($params = [])
    {
        header('Content-Type: application/json');

        Auth::requireAuth(['SUPER_ADMIN']);

        $id = $params['id'] ?? null;
        $rawInput = file_get_contents('php://input');
        $requestData = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($requestData)) {
            $requestData = $_POST;
        }

        if (!is_array($requestData)) {
            $requestData = [];
        }

        $resultado = $this->encuestaService->actualizarEncuesta($id, $requestData);

        if ($resultado['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $resultado['data'],
                'message' => 'Encuesta actualizada correctamente',
            ]);
            return;
        }

        http_response_code($resultado['status'] ?? 400);
        echo json_encode([
            'success' => false,
            'data' => ['errors' => $resultado['errors'] ?? []],
            'message' => $resultado['message'] ?? 'Error al actualizar la encuesta',
        ]);
    }

    public function registrar()
    {
        // Asegurar que la respuesta sea JSON
        header('Content-Type: application/json');

        // 1. Recibir datos del formulario
        // Primero intentamos JSON, luego POST tradicional
        $rawInput = file_get_contents('php://input');
        $requestData = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($requestData) || empty($requestData)) {
            $requestData = $_POST;
        }

        if (!is_array($requestData)) {
            $requestData = [];
        }

        // DEBUG: Verificar que campos vienen y si hay archivo
        // error_log("[ENCUESTA DEBUG] Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'unknown'));
        // error_log("[ENCUESTA DEBUG] requestData keys: " . implode(',', array_keys($requestData)));
        // error_log("[ENCUESTA DEBUG] FILES keys: " . implode(',', array_keys($_FILES)));
        // error_log("[ENCUESTA DEBUG] cedula en requestData: " . ($requestData['cedula'] ?? 'NO'));

        // Multi-tenant estricto: el instituto debe venir explícito.
        // Aceptamos: Header X-Instituto-Id / X-Tenant-Id, query ?instituto_id=, o body instituto_id.
        if (!isset($requestData['instituto_id']) || !is_numeric($requestData['instituto_id'])) {

            $institutoId = TenantContext::resolveInstitutoId($requestData, false);

            if ($institutoId !== null) {
                $requestData['instituto_id'] = $institutoId;

            } else {
                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'data' => [
                        'errors' => [
                            'instituto_id' => ['Falta el id del instituto (tenant).'],
                        ],
                    ],
                    'message' => 'Error al registrar la encuesta',
                ]);
                return;
            }
        }

        // Verificar que el instituto tenga encuestas activas (solo para público, no para admins)
        $actor = Auth::getActorIfAuthenticated();
        if (!$actor) {
            $institutoId = (int)$requestData['instituto_id'];
            $institutoModel = new \App\Models\InstitutoModel();
            $encuestaActiva = $institutoModel->getEncuestaActivaById($institutoId);
            if ($encuestaActiva === false) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'errors' => [
                            'instituto' => ['Las encuestas están temporalmente desactivadas para este instituto.'],
                        ],
                    ],
                    'message' => 'Encuestas desactivadas',
                ]);
                return;
            }
        }

        // 2. Procesar archivo de cédula (si fue enviado)
        $uploadResult = $this->handleCedulaUpload($requestData);
        
        // DEBUG
        // error_log("[ENCUESTA DEBUG] uploadResult: " . json_encode($uploadResult));

        if (!$uploadResult['success']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'data' => [
                    'errors' => [
                        'foto_cedula' => [$uploadResult['message']],
                    ],
                ],
                'message' => 'Error al registrar la encuesta',
            ]);
            return;
        }

        if (!empty($uploadResult['url_cedula'])) {
            $requestData['url_cedula'] = $uploadResult['url_cedula'];
        }

        // DEBUG
        // error_log("[ENCUESTA DEBUG] Antes de llamar al servicio, requestData['url_cedula'] = " . ($requestData['url_cedula'] ?? 'NULL'));

        // 3. Llamar al servicio para registrar la encuesta
        $resultado = $this->encuestaService->registrarEncuesta($requestData);

        // 4. Responder según el resultado en formato JSON estándar
        if ($resultado['success']) {
            http_response_code(200);

            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $resultado['id'],
                    'instituto_id' => isset($requestData['instituto_id']) ? (int)$requestData['instituto_id'] : null,
                ],
                'message' => 'Encuesta registrada exitosamente'
            ]);

        } else {
            http_response_code(400);
            
            echo json_encode([
                'success' => false,
                'data' => [
                    'errors' => $resultado['errors']
                ],
                'message' => 'Error al registrar la encuesta'
            ]);
        }
    }

    private function handleCedulaUpload(array $requestData)
    {
        if (!isset($_FILES['foto_cedula']) || !is_array($_FILES['foto_cedula'])) {
            return ['success' => true, 'url_cedula' => null];
        }

        $file = $_FILES['foto_cedula'];
        $error = isset($file['error']) ? (int)$file['error'] : UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'url_cedula' => null];
        }

        if ($error !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No se pudo subir la foto de la cédula.'];
        }

        $tmpName = isset($file['tmp_name']) ? (string)$file['tmp_name'] : '';
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return ['success' => false, 'message' => 'Archivo de cédula inválido.'];
        }

        $size = isset($file['size']) ? (int)$file['size'] : 0;
        $maxSize = 5 * 1024 * 1024;
        if ($size <= 0 || $size > $maxSize) {
            return ['success' => false, 'message' => 'La foto de la cédula debe pesar máximo 5MB.'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            return ['success' => false, 'message' => 'Formato no permitido. Solo JPG, PNG o WEBP.'];
        }

        $extension = $allowed[$mime];
        $cedula = isset($requestData['cedula']) ? preg_replace('/[^0-9]/', '', (string)$requestData['cedula']) : 'sincedula';
        if ($cedula === '') {
            $cedula = 'sincedula';
        }

        $nombre = isset($requestData['nombres']) ? (string)$requestData['nombres'] : '';
        $apellido = isset($requestData['apellidos']) ? (string)$requestData['apellidos'] : '';

        $nombreToken = $this->sanitizeNameToken($nombre, 'Nombre');
        $apellidoToken = $this->sanitizeNameToken($apellido, 'Apellido');

        $baseName = sprintf('Ci_%s_%s_%s', $cedula, $nombreToken, $apellidoToken);

        $uploadDir = $this->uploadDir;

        if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'message' => 'No se pudo preparar el directorio de carga.'];
        }

        $fileName = $this->buildUniqueFileName($uploadDir, $baseName, $extension);
        $destPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($tmpName, $destPath)) {
            return ['success' => false, 'message' => 'No se pudo guardar la foto de la cédula.'];
        }

        $publicUrl = rtrim($this->uploadPublicBase, '/') . '/' . $fileName;
        return ['success' => true, 'url_cedula' => $publicUrl];
    }

    private function sanitizeNameToken($value, $default)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $default;
        }

        $parts = preg_split('/\s+/', $value);
        $token = is_array($parts) && !empty($parts[0]) ? (string)$parts[0] : $value;

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $token);
            if ($converted !== false) {
                $token = $converted;
            }
        }

        $token = preg_replace('/[^A-Za-z0-9]/', '', (string)$token);
        if ($token === null || $token === '') {
            return $default;
        }

        $token = strtolower($token);
        return ucfirst($token);
    }

    private function buildUniqueFileName($uploadDir, $baseName, $extension)
    {
        $counter = 0;

        do {
            $suffix = $counter === 0 ? '' : '_' . ($counter + 1);
            $candidate = $baseName . $suffix . '.' . $extension;
            $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $candidate;
            $counter++;
        } while (file_exists($fullPath));

        return $candidate;
    }

    public function checkDuplicados($params = [])
    {
        header('Content-Type: application/json');

        $cedula = isset($_GET['cedula']) ? (string)$_GET['cedula'] : '';
        $email = isset($_GET['email']) ? (string)$_GET['email'] : '';

        $cedula = trim($cedula);
        $email = trim($email);

        $errors = [];

        if ($cedula !== '' && !preg_match('/^[0-9]{7,8}$/', $cedula)) {
            $errors['cedula'] = ['La cédula debe tener 7 u 8 dígitos.'];
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['El email no es válido.'];
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'data' => [
                    'errors' => $errors,
                ],
                'message' => 'Datos inválidos',
            ]);
            return;
        }

        $result = $this->encuestaService->checkDuplicados($cedula, $email);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $result,
            'message' => 'OK',
        ]);
    }
}
