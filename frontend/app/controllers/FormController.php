<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Encuesta;
use App\Services\ApiService;

/**
 * FormController - Controlador para el formulario socioeconómico
 */
class FormController extends Controller
{
    private $apiService;

    public function __construct()
    {
        $this->apiService = new ApiService();
    }

    private function configurarTenantPorSede($sede)
    {
        if (empty($sede)) {
            return;
        }

        $sedeKey = strtolower(trim((string)$sede));

        // Opción recomendada: enviar la sede/siglas y dejar que el backend resuelva el instituto_id por BD.
        // Backend soporta: X-Instituto-Siglas / X-Tenant-Code.
        if ($sedeKey !== '') {
            $this->apiService->setHeader('X-Instituto-Siglas', $sedeKey);
        }

        if (defined('SEDE_INSTITUTO_MAP') && is_array(SEDE_INSTITUTO_MAP) && isset(SEDE_INSTITUTO_MAP[$sedeKey])) {
            $institutoId = SEDE_INSTITUTO_MAP[$sedeKey];

            if (is_numeric($institutoId) && (int)$institutoId > 0) {
                $this->apiService->setHeader('X-Instituto-Id', (string)((int)$institutoId));
            }
        }
    }

    /**
     * Muestra el formulario con todos los catálogos
     */
    public function index($sede = '')
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si la URL trae una sede (/:sede/formulario), la usamos para definir el tenant del backend.
        $this->configurarTenantPorSede($sede);

        if (!empty($sede)) {
            $_SESSION['sede_actual'] = $sede;
        }

        // Obtener errores y datos antiguos de sesión
        $errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
        $oldData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

        // Limpiar sesión
        unset($_SESSION['errors']);
        unset($_SESSION['form_data']);
        $sedeActual = isset($_SESSION['sede_actual']) ? $_SESSION['sede_actual'] : '';
        $this->closeSession();

        // Cargar todos los catálogos desde la API
        $catalogos = $this->cargarCatalogos();

        // $logEntry = [
        //     'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
        //     'context' => 'FormController::index',
        //     'instituto_id_header' => $effectiveHeaders['X-Instituto-Id'] ?? 'null',
        //     'authorization_header' => $effectiveHeaders['Authorization'] ?? 'null',
        //     'tenant_instituto_id' => $tenantInstitutoId ?? 'null',
        //     'catalogos' => $catalogos,
        //     'sedeActual' => $sedeActual,
        //     'oldData' => $oldData,
        //     'errors' => $errors
        // ];

        // $logLine = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . str_repeat('-', 80) . PHP_EOL;

        // file_put_contents(__DIR__ . '/debug_index.log', $logLine, FILE_APPEND | LOCK_EX);

        $this->view('form/index', [
            'errors' => $errors,
            'old' => $oldData,
            'catalogos' => $catalogos,
            'sede' => $sedeActual
        ]);
    }


    /**
     * Carga todos los catálogos desde la API en una sola petición
     */
    private function cargarCatalogos()
    {
        try {
            $response = $this->apiService->get('/catalogo/all');
            if ($response['success'] && isset($response['data']['data'])) {
                return $response['data']['data'];
            }
        } catch (\Exception $e) {
        }

        return [
            'nacionalidad' => [], 'sexo' => [], 'tipo_estudiante' => [], 'carrera' => [],
            'semestre' => [], 'estado_civil' => [], 'condicion_laboral' => [],
            'relacion_laboral' => [], 'tipo_organizacion' => [], 'sector_trabajo' => [],
            'categoria_ocupacional' => [], 'tipo_convivencia' => [], 'tipo_vivienda' => [],
            'tenencia_vivienda' => [], 'ambiente_vivienda' => [], 'activo_vivienda' => [],
            'servicio_vivienda' => [], 'frecuencia_agua' => [], 'frecuencia_aseo' => [],
            'frecuencia_electricidad' => [], 'frecuencia_gas' => [], 'transporte' => [],
            'dependencia_economica' => [], 'fuente_ingreso' => [], 'ingreso_familiar' => [],
            'nivel_educacion' => [], 'tipo_empresa' => [], 'veracidad' => [], 'tipo_beca' => [],
        ];
    }

    /**
     * Procesa el envío del formulario
     */
    public function submit($sede = '')
    {

        if (!$this->isPost()) {
            $redirectPath = !empty($sede) ? BASE_URL . "/{$sede}/formulario" : BASE_URL . '/';
            $this->redirect($redirectPath);
            return;
        }

        // Unir prefijo y teléfono si ambos existen
        if (isset($_POST['prefijo']) && isset($_POST['telefono'])) {
            $prefijo = preg_replace('/[^0-9]/', '', (string)$_POST['prefijo']);
            $telefono = preg_replace('/[^0-9]/', '', (string)$_POST['telefono']);

            // Si ya viene completo (11 dígitos), no concatenamos prefijo.
            if (strlen($telefono) === 11) {
                $_POST['telefono'] = $telefono;
            } elseif (strlen($telefono) === 7 && strlen($prefijo) === 4) {
                $_POST['telefono'] = $prefijo . $telefono;
            } else {
                // Mantener lo enviado para que falle con mensaje nativo de HTML5 o validación del modelo.
                $_POST['telefono'] = $telefono;
            }

            unset($_POST['prefijo']);
        }

        // Asegurar tenant para la llamada al backend en base a la sede de la URL
        $this->configurarTenantPorSede($sede);

        // Crear modelo con datos del formulario
        $encuesta = new Encuesta($_POST);

        // Agregamos la sede al modelo
        if (!empty($sede)) {
            $encuesta->set('sede', $sede); // suponiendo que el backend reciba la sede o la enviemos
        }

        // Validar datos
        if (!$encuesta->validate()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['errors'] = $encuesta->getErrors();
            $_SESSION['form_data'] = $_POST;
            $this->closeSession();
            $redirectPath = !empty($sede) ? BASE_URL . "/{$sede}/formulario" : BASE_URL . '/';
            $this->redirect($redirectPath);
            return;
        }

        try {
            $uploadError = $this->validateFotoCedulaUpload();
            if ($uploadError !== null) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['errors'] = [$uploadError];
                $_SESSION['form_data'] = $_POST;
                $this->closeSession();
                $redirectPath = !empty($sede) ? BASE_URL . "/{$sede}/formulario" : BASE_URL . '/';
                $this->redirect($redirectPath);
                return;
            }

            $payload = $encuesta->toArray();

            if (isset($_FILES['foto_cedula'])
                && is_array($_FILES['foto_cedula'])
                && isset($_FILES['foto_cedula']['error'])
                && (int)$_FILES['foto_cedula']['error'] === UPLOAD_ERR_OK
            ) {
                $tmpName = isset($_FILES['foto_cedula']['tmp_name']) ? (string)$_FILES['foto_cedula']['tmp_name'] : '';
                $mimeType = isset($_FILES['foto_cedula']['type']) ? (string)$_FILES['foto_cedula']['type'] : 'application/octet-stream';
                $originalName = isset($_FILES['foto_cedula']['name']) ? (string)$_FILES['foto_cedula']['name'] : 'cedula';

                if ($tmpName !== '' && is_file($tmpName)) {
                    $payload['foto_cedula'] = new \CURLFile($tmpName, $mimeType, $originalName);
                }
            }

            // Enviar datos a la API
            $response = $this->apiService->post('/encuesta', $payload);

            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;
            $payloadSuccess = is_array($payload) && array_key_exists('success', $payload) ? (bool)$payload['success'] : null;

            // Éxito real: HTTP 2xx y (si existe) payload.success === true
            if (!empty($response['success']) && ($payloadSuccess === null || $payloadSuccess === true)) {
                // Guardar datos de la encuesta en sesión para mostrar en success
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['encuesta_enviada'] = [
                    'nombres' => $encuesta->get('nombres'),
                    'apellidos' => $encuesta->get('apellidos'),
                    'cedula' => $encuesta->get('cedula')
                ];
                $this->closeSession();

                $this->redirect(BASE_URL . '/success');
            } else {
                // Manejar error de la API
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $errors = [];

                // El backend responde: { success: false, data: { errors: {...} }, message: '...' }
                if (is_array($payload) && isset($payload['data']['errors']) && is_array($payload['data']['errors'])) {
                    foreach ($payload['data']['errors'] as $fieldErrors) {
                        if (is_array($fieldErrors)) {
                            foreach ($fieldErrors as $msg) {
                                if (is_string($msg) && trim($msg) !== '') {
                                    $errors[] = trim($msg);
                                }
                            }
                        } elseif (is_string($fieldErrors) && trim($fieldErrors) !== '') {
                            $errors[] = trim($fieldErrors);
                        }
                    }
                }

                if (empty($errors)) {
                    // Preferir message del backend; si no, mostrar status HTTP.
                    $errorMsg = (is_array($payload) && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '')
                        ? trim($payload['message'])
                        : null;

                    if ($errorMsg === null) {
                        $status = isset($response['status']) ? (int)$response['status'] : 0;
                        $errorMsg = $status > 0
                            ? 'Error al procesar el formulario (HTTP ' . $status . '). Intente nuevamente.'
                            : 'Error al procesar el formulario. Intente nuevamente.';
                    }
                    $errors[] = $errorMsg;
                }

                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                $this->closeSession();
                $redirectPath = !empty($sede) ? BASE_URL . "/{$sede}/formulario" : BASE_URL . '/';
                $this->redirect($redirectPath);
            }
        } catch (\Exception $e) {
            // Manejar excepción
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['errors'] = ['general' => 'Error de conexión con el servidor: ' . $e->getMessage()];
            $_SESSION['form_data'] = $_POST;
            $this->closeSession();
            $redirectPath = !empty($sede) ? BASE_URL . "/{$sede}/formulario" : BASE_URL . '/';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Muestra la página de éxito
     */
    public function success()
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $encuestaData = isset($_SESSION['encuesta_enviada']) ? $_SESSION['encuesta_enviada'] : null;
        unset($_SESSION['encuesta_enviada']);
        $this->closeSession();

        if (!$encuestaData) {
            $this->redirect(BASE_URL . '/');
            return;
        }

        $this->view('form/success', [
            'encuesta' => $encuestaData
        ]);
    }

    public function checkDuplicados()
    {
        header('Content-Type: application/json; charset=UTF-8');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sedeActual = isset($_SESSION['sede_actual']) ? (string)$_SESSION['sede_actual'] : '';
        $this->configurarTenantPorSede($sedeActual);
        $this->closeSession();

        $params = [];
        if (isset($_GET['cedula']) && trim((string)$_GET['cedula']) !== '') {
            $params['cedula'] = trim((string)$_GET['cedula']);
        }
        if (isset($_GET['email']) && trim((string)$_GET['email']) !== '') {
            $params['email'] = trim((string)$_GET['email']);
        }

        try {
            $response = $this->apiService->get('/encuesta/check', $params);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (is_array($payload) && array_key_exists('success', $payload)) {
                http_response_code(!empty($response['success']) ? 200 : (isset($response['status']) ? (int)$response['status'] : 400));
                echo json_encode($payload);
                return;
            }

            if (!empty($response['success']) && is_array($payload)) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'data' => $payload,
                    'message' => 'Validación de duplicados completada',
                ]);
                return;
            }

            http_response_code(isset($response['status']) ? (int)$response['status'] : 400);
            echo json_encode([
                'success' => false,
                'data' => [
                    'errors' => ['No se pudo validar duplicados en este momento.'],
                ],
                'message' => 'Error al validar duplicados',
            ]);
        } catch (\Exception $e) {
            http_response_code(503);
            echo json_encode([
                'success' => false,
                'data' => [
                    'errors' => ['Servicio de validación no disponible.'],
                ],
                'message' => 'Error de conexión al validar duplicados',
            ]);
        }
    }

    private function validateFotoCedulaUpload()
    {
        if (!isset($_FILES['foto_cedula']) || !is_array($_FILES['foto_cedula'])) {
            return null;
        }

        $upload = $_FILES['foto_cedula'];
        $error = isset($upload['error']) ? (int)$upload['error'] : UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            return 'No se pudo procesar la foto de la cédula. Intente de nuevo.';
        }

        $size = isset($upload['size']) ? (int)$upload['size'] : 0;
        $maxSize = 5 * 1024 * 1024;
        if ($size > $maxSize) {
            return 'La foto de la cédula supera el tamaño máximo de 5MB.';
        }

        return null;
    }

}
