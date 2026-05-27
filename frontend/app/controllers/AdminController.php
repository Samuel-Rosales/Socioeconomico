<?php

namespace App\Controllers;

use Core\Controller;
use Utils\LogDebugger;
use App\Services\ApiService;
use App\Services\CatalogoService;
use App\Services\UsuarioService;
use App\Services\EncuestaService;
use App\Exceptions\SessionExpiredException;

/**
 * AdminController - Controlador para el Dashboard Administrativo
 */
class AdminController extends Controller
{
    private $apiService;
    private $catalogoService;
    private $usuarioService;
    private $encuestaService;

    public function __construct()
    {
        $this->apiService = new ApiService();
        $this->catalogoService = new CatalogoService($this->apiService);
        $this->usuarioService = new UsuarioService($this->apiService);
        $this->encuestaService = new EncuestaService($this->apiService);
    }

    private function handleSessionExpired(SessionExpiredException $e)
    {
        $this->clearAuthSession();
        $_SESSION['login_error'] = $e->getMessage();
        $this->closeSession();
        $this->redirect(BASE_URL . '/login');
        exit;
    }

    /**
     * Verifica la autenticación antes de ejecutar un método
     */
    private function checkAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect(BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Ejecuta una callable verificando excepciones de sesión
     */
    private function withSessionCheck(callable $callback)
    {
        try {
            return $callback();
        } catch (SessionExpiredException $e) {
            $this->handleSessionExpired($e);
        }
    }

    /**
     * Verifica si existe una sesión activa
     */
    private function isAuthenticated()
    {
        return $this->hasValidAuthSession();
    }

    private function actorRolCodigo()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $authUser = isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']) ? $_SESSION['auth_user'] : [];
        
        if (isset($authUser['rol']) && is_array($authUser['rol']) && !empty($authUser['rol']['codigo'])) {
            $rol = (string)$authUser['rol']['codigo'];
            $this->closeSession();
            return $rol;
        }
        $this->closeSession();
        return null;
    }

    public function heartbeat()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['auth_token'])) {
            $this->updateLastActivity();
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'last_activity' => $_SESSION['last_activity'] ?? time(), 'timeout' => SESSION_TIMEOUT]);
        exit;
    }

    public function cedulaFile($filename)
    {
        $this->checkAuth();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $filename = is_array($filename) && isset($filename['filename']) ? (string)$filename['filename'] : (string)$filename;
        $filename = basename(trim($filename));

        if ($filename === '' || !preg_match('/^[A-Za-z0-9._-]+\.(jpe?g|png|webp)$/i', $filename)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Archivo inválido.',
            ]);
            return;
        }

        $token = isset($_SESSION['auth_token']) ? (string)$_SESSION['auth_token'] : '';
        if (trim($token) === '') {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $backendUrl = rtrim((string)API_BASE_URL, '/') . '/cedulas/' . rawurlencode($filename);
        $ch = curl_init($backendUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener la imagen.',
            ]);
            return;
        }

        if ($httpCode !== 200) {
            http_response_code($httpCode ?: 500);
            if (is_string($body) && trim($body) !== '') {
                header('Content-Type: application/json');
                echo $body;
            }
            return;
        }

        http_response_code(200);
        if ($contentType !== '') {
            header('Content-Type: ' . $contentType);
        }
        header('Cache-Control: private, max-age=300');
        header('X-Content-Type-Options: nosniff');
        echo $body;
        exit;
    }

    private function requireSuperAdmin()
    {
        $rol = $this->actorRolCodigo();
        if ($rol !== 'SUPER_ADMIN') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['flash_type'] = 'error';
            $_SESSION['flash_message'] = 'No autorizado: solo SUPER_ADMIN puede acceder a esta sección.';
            $this->closeSession();
            $this->redirect(BASE_URL . '/admin');
        }
    }

    /**
     * Vista principal del Dashboard
     */
    public function index()
    {
        $this->checkAuth();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $dashboard = [
            'total_encuestas' => null,
            'total_usuarios' => null,
            'ultima_encuesta' => null,
        ];

        $this->closeSession();

        $encuestasRecientes = [];
        $apiError = null;

        try {
            $encuestasRecientes = $this->encuestaService->ultimas(5);

            // Total de encuestas (sin inferir estados como "pendiente")
            $dashboard['total_encuestas'] = $this->encuestaService->totalPorFiltro(null);

            // Fecha de la última encuesta (si el endpoint devuelve orden descendente)
            if (!empty($encuestasRecientes) && is_array($encuestasRecientes[0]) && !empty($encuestasRecientes[0]['creado'])) {
                $dashboard['ultima_encuesta'] = (string)$encuestasRecientes[0]['creado'];
            }

            // $logEntry = [
            //     'timestamp' => date('Y-m-d H:i:s'),
            //     'action' => 'Acceso a Dashboard',
            //     'user_id' => $_SESSION['auth_user']['id'] ?? null,
            //     'user_ci' => $_SESSION['auth_user']['ci'] ?? null,
            //     'user_nombre' => $_SESSION['auth_user']['nombre_completo'] ?? null,
            //     'codigo_rol' => $_SESSION['auth_user']['rol']['codigo'] ?? null,
            //     'codigo' => $_SESSION['codigo'] ?? null,
            // ];

            // LogDebugger::log($logEntry, 'admin_access');

            if(isset($_SESSION) && isset($_SESSION['auth_user']) && $_SESSION['auth_user']['rol']['codigo'] === 'SUPER_ADMIN') { 
                               
                // Total de usuarios
                $usuariosResponse = $this->usuarioService->listar();
                $usuariosPayload = isset($usuariosResponse['data']) && is_array($usuariosResponse['data']) ? $usuariosResponse['data'] : null;
                
                if (!empty($usuariosResponse['success']) && $usuariosPayload) {
                    $usuariosData = (isset($usuariosPayload['success']) && array_key_exists('data', $usuariosPayload) && is_array($usuariosPayload['data']))
                        ? $usuariosPayload['data']
                        : $usuariosPayload;
                    $usuariosItems = isset($usuariosData['items']) && is_array($usuariosData['items']) ? $usuariosData['items'] : [];
                    $dashboard['total_usuarios'] = count($usuariosItems);
                }
            }
        } catch (\Exception $e) {
            $apiError = [
                'status' => 0,
                'message' => 'Error de conexión con el servidor: ' . $e->getMessage(),
            ];
        }
        
        // Renderizar vista usando el layout 'admin'
        $this->view('admin/dashboard', [
            'title' => 'Dashboard | Admin',
            'current_page' => 'dashboard',
            'dashboard' => $dashboard,
            'encuestasRecientes' => $encuestasRecientes,
            'apiError' => $apiError,
        ], 'admin');
    }

    /**
     * Vista de estadísticas (maquetación con datos mock)
     */
    public function estadisticas()
    {
        $this->checkAuth();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $vista = isset($_GET['vista']) ? trim((string)$_GET['vista']) : '';
        $allowedVistas = ['resumen', 'estratos', 'carreras'];
        if ($vista === '' || !in_array($vista, $allowedVistas, true)) {
            $vista = 'resumen';
        }

        $from = isset($_GET['from']) ? trim((string)$_GET['from']) : '';
        $to = isset($_GET['to']) ? trim((string)$_GET['to']) : '';

        $parseDate = function ($value) {
            if (!is_string($value) || trim($value) === '') {
                return null;
            }
            $dt = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$dt) {
                return null;
            }
            $errors = \DateTime::getLastErrors();
            if (!empty($errors['warning_count']) || !empty($errors['error_count'])) {
                return null;
            }
            $dt->setTime(0, 0, 0);
            return $dt;
        };

        $toDt = $parseDate($to);
        if ($toDt === null) {
            $toDt = new \DateTime('today');
        }

        $fromDt = $parseDate($from);
        if ($fromDt === null) {
            $fromDt = (clone $toDt)->modify('-29 days');
        }

        if ($fromDt > $toDt) {
            $tmp = $fromDt;
            $fromDt = $toDt;
            $toDt = $tmp;
        }

        $this->closeSession();

        // Evitar rangos excesivos en mock
        $maxDays = 366;
        $diffDays = (int)$fromDt->diff($toDt)->format('%a') + 1;
        if ($diffDays > $maxDays) {
            $fromDt = (clone $toDt)->modify('-' . ($maxDays - 1) . ' days');
            $diffDays = $maxDays;
        }

        // Serie temporal mock: encuestas por día
        $labels = [];
        $series = [];
        $cursor = clone $fromDt;
        while ($cursor <= $toDt) {
            $label = $cursor->format('Y-m-d');
            $labels[] = $label;
            $seed = abs((int)crc32('encuestas:' . $label));
            $value = 8 + ($seed % 17); // 8..24
            // Simular fines de semana más bajos
            $dow = (int)$cursor->format('N');
            if ($dow >= 6) {
                $value = (int)max(1, floor($value * 0.65));
            }
            $series[] = $value;
            $cursor->modify('+1 day');
        }

        $totalEncuestas = array_sum($series);
        $dias = count($series);
        $promedioDiario = $dias > 0 ? ($totalEncuestas / $dias) : 0;
        $maxDia = !empty($series) ? max($series) : 0;

        // Distribución mock de estratos (1..5)
        $estratoWeights = [
            1 => 0.14,
            2 => 0.24,
            3 => 0.30,
            4 => 0.20,
            5 => 0.12,
        ];
        $estratos = [];
        $assigned = 0;
        foreach ($estratoWeights as $estrato => $w) {
            $count = (int)floor($totalEncuestas * $w);
            $estratos[(string)$estrato] = $count;
            $assigned += $count;
        }
        // Ajustar remainder para cuadrar con el total
        $remainder = $totalEncuestas - $assigned;
        $estratoKeys = array_keys($estratos);
        $i = 0;
        while ($remainder > 0 && !empty($estratoKeys)) {
            $k = $estratoKeys[$i % count($estratoKeys)];
            $estratos[$k] += 1;
            $remainder--;
            $i++;
        }

        // Distribución mock por carreras
        $carrerasBase = [
            'Informática',
            'Administración',
            'Contaduría',
            'Educación',
            'Comunicación Social',
            'Enfermería',
            'Psicología',
        ];
        $carreraWeights = [0.18, 0.16, 0.13, 0.12, 0.11, 0.10, 0.20];
        $carreras = [];
        $assigned = 0;
        foreach ($carrerasBase as $idx => $name) {
            $w = isset($carreraWeights[$idx]) ? (float)$carreraWeights[$idx] : 0.1;
            $count = (int)floor($totalEncuestas * $w);
            $carreras[$name] = $count;
            $assigned += $count;
        }
        $remainder = $totalEncuestas - $assigned;
        $carreraKeys = array_keys($carreras);
        $i = 0;
        while ($remainder > 0 && !empty($carreraKeys)) {
            $k = $carreraKeys[$i % count($carreraKeys)];
            $carreras[$k] += 1;
            $remainder--;
            $i++;
        }

        $this->view('admin/estadisticas', [
            'title' => 'Estadísticas | Admin',
            'current_page' => 'stats_' . $vista,
            'stats_view' => $vista,
            'filters' => [
                'from' => $fromDt->format('Y-m-d'),
                'to' => $toDt->format('Y-m-d'),
            ],
            'kpis' => [
                'total_encuestas' => $totalEncuestas,
                'promedio_diario' => $promedioDiario,
                'max_dia' => $maxDia,
                'dias' => $dias,
            ],
            'charts' => [
                'timeline' => [
                    'labels' => $labels,
                    'values' => $series,
                ],
                'estratos' => $estratos,
                'carreras' => $carreras,
            ],
        ], 'admin');
    }

    /**
     * Vista de gestión de usuarios
     */
    public function users()
    {
        $this->checkAuth();
        $this->requireSuperAdmin();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        $perPage = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        if ($perPage < 1) {
            $perPage = 10;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $flash = null;
        if (!empty($_SESSION['flash_message'])) {
            $flash = [
                'type' => isset($_SESSION['flash_type']) ? (string)$_SESSION['flash_type'] : 'info',
                'message' => (string)$_SESSION['flash_message'],
                'errors' => isset($_SESSION['flash_errors']) && is_array($_SESSION['flash_errors']) ? $_SESSION['flash_errors'] : [],
            ];
        }
        unset($_SESSION['flash_type'], $_SESSION['flash_message'], $_SESSION['flash_errors']);

        $authUser = isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']) ? $_SESSION['auth_user'] : [];
        
        $actorRol = null;

        if (isset($authUser['rol']) && is_array($authUser['rol']) && !empty($authUser['rol']['codigo'])) {
            $actorRol = (string)$authUser['rol']['codigo'];
        }

        $this->closeSession();

        $usuarios = [
            'items' => [],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'total_pages' => 1,
            ],
        ];

        $apiError = null;

        try {
            $response = $this->usuarioService->listar();
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if (!empty($response['success']) && $payload) {
                $data = (isset($payload['success']) && array_key_exists('data', $payload) && is_array($payload['data']))
                    ? $payload['data']
                    : $payload;

                $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

                if ($q !== '') {
                    $qLower = mb_strtolower($q, 'UTF-8');
                    $items = array_values(array_filter($items, function ($u) use ($qLower) {
                        $ci = isset($u['ci']) ? mb_strtolower((string)$u['ci'], 'UTF-8') : '';
                        $nombre = isset($u['nombre_completo']) ? mb_strtolower((string)$u['nombre_completo'], 'UTF-8') : '';
                        $rol = isset($u['rol_nombre']) ? mb_strtolower((string)$u['rol_nombre'], 'UTF-8') : '';
                        $rolCodigo = isset($u['rol_codigo']) ? mb_strtolower((string)$u['rol_codigo'], 'UTF-8') : '';
                        return (strpos($ci, $qLower) !== false)
                            || (strpos($nombre, $qLower) !== false)
                            || (strpos($rol, $qLower) !== false)
                            || (strpos($rolCodigo, $qLower) !== false);
                    }));
                }

                $total = count($items);
                $totalPages = (int)ceil($total / $perPage);
                if ($totalPages < 1) {
                    $totalPages = 1;
                }
                if ($page > $totalPages) {
                    $page = $totalPages;
                }

                $offset = ($page - 1) * $perPage;
                $usuarios['items'] = array_slice($items, $offset, $perPage);
                $usuarios['pagination'] = [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                ];
            } else {
                $message = 'No se pudieron cargar los usuarios.';
                if (is_array($payload) && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                    $message = $payload['message'];
                }

                $apiError = [
                    'status' => isset($response['status']) ? (int)$response['status'] : 0,
                    'message' => $message,
                ];
            }
        } catch (\Exception $e) {
            $apiError = [
                'status' => 0,
                'message' => 'Error de conexión con el servidor: ' . $e->getMessage(),
            ];
        }

        $roles = [];
        $institutos = [];

        try {
            $rolesResponse = $this->catalogoService->roles();
            $rolesPayload = isset($rolesResponse['data']) && is_array($rolesResponse['data']) ? $rolesResponse['data'] : null;
            if (!empty($rolesResponse['success']) && $rolesPayload) {
                $rolesData = (isset($rolesPayload['success']) && array_key_exists('data', $rolesPayload) && is_array($rolesPayload['data']))
                    ? $rolesPayload['data']
                    : $rolesPayload;
                if (is_array($rolesData)) {
                    $roles = $rolesData;
                }
            }
        } catch (\Exception $e) {
            $roles = [];
        }

        // Solo SUPER_ADMIN necesita selector de institutos
        if ($actorRol === 'SUPER_ADMIN') {
            try {
                $instResponse = $this->catalogoService->institutos();
                $instPayload = isset($instResponse['data']) && is_array($instResponse['data']) ? $instResponse['data'] : null;
                if (!empty($instResponse['success']) && $instPayload) {
                    $instData = (isset($instPayload['success']) && array_key_exists('data', $instPayload) && is_array($instPayload['data']))
                        ? $instPayload['data']
                        : $instPayload;
                    if (is_array($instData)) {
                        $institutos = $instData;
                    }
                }
            } catch (\Exception $e) {
                $institutos = [];
            }
        }

        $this->view('admin/users', [
            'title' => 'Gestión de Usuarios | Admin',
            'current_page' => 'users',
            'usuarios' => $usuarios,
            'roles' => $roles,
            'institutos' => $institutos,
            'apiError' => $apiError,
            'filters' => [
                'q' => $q,
                'page' => $page,
                'per_page' => $perPage,
            ],
            'actorRol' => $actorRol,
            'flash' => $flash,
        ], 'admin');
    }

    /**
     * Vista de gestión de respuestas
     */
    public function responses()
    {
        $this->checkAuth();

        // Filtros/paginación vía querystring
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $carreraId = isset($_GET['carrera_id']) ? trim((string)$_GET['carrera_id']) : '';
        $estrato = isset($_GET['estrato']) ? trim((string)$_GET['estrato']) : '';
        // Compatibilidad: UI anterior enviaba "estado".
        if ($estrato === '' && isset($_GET['estado'])) {
            $estrato = trim((string)$_GET['estado']);
        }

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        $perPage = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        if ($perPage < 1) {
            $perPage = 10;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        $params = [
            'page' => $page,
            'per_page' => $perPage,
        ];
        if ($q !== '') {
            $params['q'] = $q;
        }
        if ($carreraId !== '' && is_numeric($carreraId) && (int)$carreraId > 0) {
            $params['carrera_id'] = (int)$carreraId;
        }
        if ($estrato !== '') {
            $params['estrato'] = $estrato;
        }

        // Cargar listado de encuestas (resumen)
        $encuestas = [
            'items' => [],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => 0,
                'total_pages' => 1,
            ],
        ];

        $apiError = null;

        try {
            // Forzar Authorization explícito (evita casos donde el header no llegue por sesión)
            if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['auth_token'])) {
                $this->apiService->setHeader('Authorization', 'Bearer ' . (string) $_SESSION['auth_token']);
            }

            $response = $this->apiService->get('/encuesta', $params);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

            if ($response['success'] && $payload) {
                // Formato estándar: {success, data, message}
                $data = (isset($payload['success']) && array_key_exists('data', $payload) && is_array($payload['data']))
                    ? $payload['data']
                    : $payload;

                if (isset($data['items']) && is_array($data['items'])) {
                    $encuestas['items'] = $data['items'];
                }
                if (isset($data['pagination']) && is_array($data['pagination'])) {
                    $encuestas['pagination'] = array_merge($encuestas['pagination'], $data['pagination']);
                }
            } else {
                $message = 'No se pudieron cargar las respuestas.';
                if (is_array($payload) && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                    $message = $payload['message'];
                }

                $apiError = [
                    'status' => isset($response['status']) ? (int)$response['status'] : 0,
                    'message' => $message,
                ];
            }
        } catch (\Exception $e) {
            $apiError = [
                'status' => 0,
                'message' => 'Error de conexión con el servidor: ' . $e->getMessage(),
            ];
        }

        // Catálogo de carreras para el filtro
        $carreras = [];
        try {
            $institutoId = null;
            if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['auth_user'])) {
                $user = $_SESSION['auth_user'];
                if (isset($user['instituto']) && is_array($user['instituto']) && !empty($user['instituto']['id'])) {
                    $institutoId = (int)$user['instituto']['id'];
                }
            }

            $catalogParams = [];
            if (!empty($institutoId)) {
                $catalogParams['instituto_id'] = $institutoId;
            }

            $catalogResponse = $this->apiService->get('/catalogo/carrera', $catalogParams);
            $catalogPayload = isset($catalogResponse['data']) && is_array($catalogResponse['data']) ? $catalogResponse['data'] : null;

            if ($catalogResponse['success'] && $catalogPayload) {
                $catalogData = (isset($catalogPayload['success']) && array_key_exists('data', $catalogPayload) && is_array($catalogPayload['data']))
                    ? $catalogPayload['data']
                    : $catalogPayload;

                if (is_array($catalogData)) {
                    $carreras = $catalogData;
                }
            }
        } catch (\Exception $e) {
            $carreras = [];
        }
        

        $this->view('admin/responses', [
            'title' => 'Respuestas a Encuestas | Admin',
            'current_page' => 'responses',
            'encuestas' => $encuestas,
            'carreras' => $carreras,
            'apiError' => $apiError,
            'filters' => [
                'q' => $q,
                'carrera_id' => $carreraId,
                'estrato' => $estrato,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ], 'admin');
    }

    public function responseDetail($id)
    {
        $this->checkAuth();

        $id = is_numeric($id) ? (int)$id : 0;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $authUser = isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']) ? $_SESSION['auth_user'] : [];
        $actorRol = (isset($authUser['rol']) && is_array($authUser['rol']) && !empty($authUser['rol']['codigo']))
            ? (string)$authUser['rol']['codigo']
            : null;
        $isSuperAdmin = ($actorRol === 'SUPER_ADMIN');
        $editMode = $isSuperAdmin && isset($_GET['edit']) && (string)$_GET['edit'] === '1';

        $encuesta = null;
        $apiError = null;
        $flash = null;
        $editCatalogs = [];

        if (!empty($_SESSION['flash_message'])) {
            $flash = [
                'type' => isset($_SESSION['flash_type']) ? (string)$_SESSION['flash_type'] : 'info',
                'message' => (string)$_SESSION['flash_message'],
                'errors' => isset($_SESSION['flash_errors']) && is_array($_SESSION['flash_errors']) ? $_SESSION['flash_errors'] : [],
            ];
        }
        unset($_SESSION['flash_type'], $_SESSION['flash_message'], $_SESSION['flash_errors']);

        if ($id <= 0) {
            $apiError = ['status' => 400, 'message' => 'ID inválido'];
        } else {
            try {
                // Forzar Authorization explícito (evita casos donde el header no llegue por sesión)
                if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['auth_token'])) {
                    $this->apiService->setHeader('Authorization', 'Bearer ' . (string) $_SESSION['auth_token']);
                }

                $response = $this->apiService->get('/encuesta/' . $id);
                $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;

                if ($response['success'] && $payload) {
                    $data = (isset($payload['success']) && array_key_exists('data', $payload) && is_array($payload['data']))
                        ? $payload['data']
                        : $payload;

                    if (is_array($data)) {
                        $encuesta = $data;
                    }
                } else {
                    $message = 'No se pudo cargar el detalle de la encuesta.';
                    if (is_array($payload) && isset($payload['message']) && is_string($payload['message']) && trim($payload['message']) !== '') {
                        $message = $payload['message'];
                    }

                    $apiError = [
                        'status' => isset($response['status']) ? (int)$response['status'] : 0,
                        'message' => $message,
                    ];
                }
            } catch (\Exception $e) {
                $apiError = [
                    'status' => 0,
                    'message' => 'Error de conexión con el servidor: ' . $e->getMessage(),
                ];
            }

            if ($isSuperAdmin && $encuesta !== null) {
            $editCatalogs = $this->buildEncuestaEditCatalogs($encuesta);
        }
    }

        $this->view('admin/response_detail', [
            'title' => 'Detalle de Encuesta | Admin',
            'current_page' => 'responses',
            'encuesta' => $encuesta,
            'apiError' => $apiError,
            'flash' => $flash,
            'isSuperAdmin' => $isSuperAdmin,
            'editMode' => $editMode,
            'editCatalogs' => $editCatalogs,
        ], 'admin');
    }

    public function responseUpdate($id)
    {
        $this->checkAuth();
        $this->requireSuperAdmin();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = is_numeric($id) ? (int)$id : 0;
        if ($id <= 0) {
            $_SESSION['flash_type'] = 'error';
            $_SESSION['flash_message'] = 'ID inválido para actualizar.';
            $this->redirect(BASE_URL . '/admin/respuestas');
            return;
        }

        $post = $_POST;

        $payload = [
            'email' => isset($post['email']) ? trim((string)$post['email']) : null,
            'nombres' => isset($post['nombres']) ? trim((string)$post['nombres']) : null,
            'apellidos' => isset($post['apellidos']) ? trim((string)$post['apellidos']) : null,
            'cedula' => isset($post['cedula']) ? trim((string)$post['cedula']) : null,
            'telefono' => isset($post['telefono']) ? trim((string)$post['telefono']) : null,
            'fecha_nacimiento' => isset($post['fecha_nacimiento']) ? trim((string)$post['fecha_nacimiento']) : null,
            'direccion' => isset($post['direccion']) ? trim((string)$post['direccion']) : null,
            'hijos' => isset($post['hijos']) ? (int)$post['hijos'] : 0,
            'numero_hijos' => isset($post['numero_hijos']) && $post['numero_hijos'] !== '' ? (int)$post['numero_hijos'] : 0,
            'discapacidad' => isset($post['discapacidad']) ? trim((string)$post['discapacidad']) : null,
            'enfermedad_cronica' => isset($post['enfermedad_cronica']) ? trim((string)$post['enfermedad_cronica']) : null,
            'estudio_fya' => isset($post['estudio_fya']) ? (int)$post['estudio_fya'] : 0,
            'numero_habitantes' => isset($post['numero_habitantes']) && $post['numero_habitantes'] !== '' ? (int)$post['numero_habitantes'] : null,
            'numero_ocupantes_familia' => isset($post['numero_ocupantes_familia']) && $post['numero_ocupantes_familia'] !== '' ? (int)$post['numero_ocupantes_familia'] : null,
            'url_cedula' => isset($post['url_cedula']) ? trim((string)$post['url_cedula']) : null,

            'instituto_id' => $this->toNullableInt(isset($post['instituto_id']) ? $post['instituto_id'] : null),
            'nacionalidad_id' => $this->toNullableInt(isset($post['nacionalidad_id']) ? $post['nacionalidad_id'] : null),
            'sexo_id' => $this->toNullableInt(isset($post['sexo_id']) ? $post['sexo_id'] : null),
            'tipo_estudiante_id' => $this->toNullableInt(isset($post['tipo_estudiante_id']) ? $post['tipo_estudiante_id'] : null),
            'carrera_id' => $this->toNullableInt(isset($post['carrera_id']) ? $post['carrera_id'] : null),
            'semestre_id' => $this->toNullableInt(isset($post['semestre_id']) ? $post['semestre_id'] : null),
            'estado_civil_id' => $this->toNullableInt(isset($post['estado_civil_id']) ? $post['estado_civil_id'] : null),
            'condicion_laboral_id' => $this->toNullableInt(isset($post['condicion_laboral_id']) ? $post['condicion_laboral_id'] : null),
            'trabajo_relacion_id' => $this->toNullableInt(isset($post['trabajo_relacion_id']) ? $post['trabajo_relacion_id'] : null),
            'tipo_organizacion_id' => $this->toNullableInt(isset($post['tipo_organizacion_id']) ? $post['tipo_organizacion_id'] : null),
            'sector_trabajo_id' => $this->toNullableInt(isset($post['sector_trabajo_id']) ? $post['sector_trabajo_id'] : null),
            'categoria_ocupacional_id' => $this->toNullableInt(isset($post['categoria_ocupacional_id']) ? $post['categoria_ocupacional_id'] : null),
            'tipo_convivencia_id' => $this->toNullableInt(isset($post['tipo_convivencia_id']) ? $post['tipo_convivencia_id'] : null),
            'tipo_vivienda_id' => $this->toNullableInt(isset($post['tipo_vivienda_id']) ? $post['tipo_vivienda_id'] : null),
            'tenencia_vivienda_id' => $this->toNullableInt(isset($post['tenencia_vivienda_id']) ? $post['tenencia_vivienda_id'] : null),
            'frecuencia_servicio_agua_id' => $this->toNullableInt(isset($post['frecuencia_servicio_agua_id']) ? $post['frecuencia_servicio_agua_id'] : null),
            'frecuencia_servicio_aseo_id' => $this->toNullableInt(isset($post['frecuencia_servicio_aseo_id']) ? $post['frecuencia_servicio_aseo_id'] : null),
            'frecuencia_servicio_electricidad_id' => $this->toNullableInt(isset($post['frecuencia_servicio_electricidad_id']) ? $post['frecuencia_servicio_electricidad_id'] : null),
            'frecuencia_servicio_gas_id' => $this->toNullableInt(isset($post['frecuencia_servicio_gas_id']) ? $post['frecuencia_servicio_gas_id'] : null),
            'transporte_id' => $this->toNullableInt(isset($post['transporte_id']) ? $post['transporte_id'] : null),
            'dependencia_economica_id' => $this->toNullableInt(isset($post['dependencia_economica_id']) ? $post['dependencia_economica_id'] : null),
            'fuente_ingreso_familiar_id' => $this->toNullableInt(isset($post['fuente_ingreso_familiar_id']) ? $post['fuente_ingreso_familiar_id'] : null),
            'ingreso_familiar_id' => $this->toNullableInt(isset($post['ingreso_familiar_id']) ? $post['ingreso_familiar_id'] : null),
            'nivel_eduacion_padre_id' => $this->toNullableInt(isset($post['nivel_eduacion_padre_id']) ? $post['nivel_eduacion_padre_id'] : null),
            'trabaja_padre' => isset($post['trabaja_padre']) ? (int)$post['trabaja_padre'] : 0,
            'tipo_empresa_padre_id' => $this->toNullableInt(isset($post['tipo_empresa_padre_id']) ? $post['tipo_empresa_padre_id'] : null),
            'categoria_ocupacional_padre_id' => $this->toNullableInt(isset($post['categoria_ocupacional_padre_id']) ? $post['categoria_ocupacional_padre_id'] : null),
            'sector_trabajo_padre_id' => $this->toNullableInt(isset($post['sector_trabajo_padre_id']) ? $post['sector_trabajo_padre_id'] : null),
            'padre_en_venezuela' => isset($post['padre_en_venezuela']) ? (int)$post['padre_en_venezuela'] : 0,
            'padre_egresado_iujo' => isset($post['padre_egresado_iujo']) ? (int)$post['padre_egresado_iujo'] : 0,
            'nivel_eduacion_madre_id' => $this->toNullableInt(isset($post['nivel_eduacion_madre_id']) ? $post['nivel_eduacion_madre_id'] : null),
            'trabaja_madre' => isset($post['trabaja_madre']) ? (int)$post['trabaja_madre'] : 0,
            'tipo_empresa_madre_id' => $this->toNullableInt(isset($post['tipo_empresa_madre_id']) ? $post['tipo_empresa_madre_id'] : null),
            'categoria_ocupacional_madre_id' => $this->toNullableInt(isset($post['categoria_ocupacional_madre_id']) ? $post['categoria_ocupacional_madre_id'] : null),
            'sector_trabajo_madre_id' => $this->toNullableInt(isset($post['sector_trabajo_madre_id']) ? $post['sector_trabajo_madre_id'] : null),
            'madre_en_venezuela' => isset($post['madre_en_venezuela']) ? (int)$post['madre_en_venezuela'] : 0,
            'madre_egresada_iujo' => isset($post['madre_egresada_iujo']) ? (int)$post['madre_egresada_iujo'] : 0,
            'veracidad_id' => $this->toNullableInt(isset($post['veracidad_id']) ? $post['veracidad_id'] : null),
            'tipo_beca_id' => $this->toNullableInt(isset($post['tipo_beca_id']) ? $post['tipo_beca_id'] : null),

            'activos_vivienda' => isset($post['activos_vivienda']) && is_array($post['activos_vivienda']) ? array_values($post['activos_vivienda']) : [],
            'ambientes_vivienda' => isset($post['ambientes_vivienda']) && is_array($post['ambientes_vivienda']) ? array_values($post['ambientes_vivienda']) : [],
            'servicios_vivienda' => isset($post['servicios_vivienda']) && is_array($post['servicios_vivienda']) ? array_values($post['servicios_vivienda']) : [],
        ];

        try {
            if (!empty($_SESSION['auth_token'])) {
                $this->apiService->setHeader('Authorization', 'Bearer ' . (string)$_SESSION['auth_token']);
            }

            $response = $this->apiService->put('/encuesta/' . $id, $payload);
            $respData = isset($response['data']) && is_array($response['data']) ? $response['data'] : [];

            if (!empty($response['success'])) {
                $_SESSION['flash_type'] = 'success';
                $_SESSION['flash_message'] = 'Encuesta actualizada correctamente.';
                $this->redirect(BASE_URL . '/admin/respuestas/' . $id);
                return;
            }

            $message = 'No se pudo actualizar la encuesta.';
            if (isset($respData['message']) && is_string($respData['message']) && trim($respData['message']) !== '') {
                $message = trim($respData['message']);
            }

            $_SESSION['flash_type'] = 'error';
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_errors'] = (isset($respData['data']) && is_array($respData['data']) && isset($respData['data']['errors']) && is_array($respData['data']['errors']))
                ? $respData['data']['errors']
                : [];

            $this->redirect(BASE_URL . '/admin/respuestas/' . $id . '?edit=1');
        } catch (\Exception $e) {
            $_SESSION['flash_type'] = 'error';
            $_SESSION['flash_message'] = 'Error de conexión con el servidor: ' . $e->getMessage();
            $this->redirect(BASE_URL . '/admin/respuestas/' . $id . '?edit=1');
        }
    }

    private function toNullableInt($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return null;
        }
        return (int)$value;
    }

    private function fetchSimpleCatalog($resource)
    {
        try {
            $response = $this->catalogoService->catalogo($resource);
            $payload = isset($response['data']) && is_array($response['data']) ? $response['data'] : null;
            if (!empty($response['success']) && $payload) {
                $data = (isset($payload['success']) && array_key_exists('data', $payload) && is_array($payload['data']))
                    ? $payload['data']
                    : $payload;
                return is_array($data) ? $data : [];
            }
        } catch (\Exception $e) {
            return [];
        }

        return [];
    }

    private function buildEncuestaEditCatalogs(array $encuesta)
    {
        $catalogs = [
            'instituto' => [],
            'nacionalidad' => [],
            'sexo' => [],
            'tipo_estudiante' => [],
            'carrera' => [],
            'semestre' => [],
            'estado_civil' => [],
            'condicion_laboral' => [],
            'relacion_laboral' => [],
            'tipo_organizacion' => [],
            'sector_trabajo' => [],
            'categoria_ocupacional' => [],
            'tipo_convivencia' => [],
            'tipo_vivienda' => [],
            'tenencia_vivienda' => [],
            'ambiente_vivienda' => [],
            'activo_vivienda' => [],
            'servicio_vivienda' => [],
            'frecuencia_agua' => [],
            'frecuencia_aseo' => [],
            'frecuencia_electricidad' => [],
            'frecuencia_gas' => [],
            'transporte' => [],
            'dependencia_economica' => [],
            'fuente_ingreso' => [],
            'ingreso_familiar' => [],
            'nivel_educacion' => [],
            'tipo_empresa' => [],
            'veracidad' => [],
            'tipo_beca' => [],
        ];

        $institutoId = null;
        if (!empty($encuesta['instituto_id']) && is_numeric($encuesta['instituto_id'])) {
            $institutoId = (int)$encuesta['instituto_id'];
        }

        try {
            $allResponse = $this->catalogoService->all(!empty($institutoId) ? ['instituto_id' => $institutoId] : []);
            $payload = isset($allResponse['data']) && is_array($allResponse['data']) ? $allResponse['data'] : null;

            if (!empty($allResponse['success']) && $payload) {
                $allData = (isset($payload['success']) && array_key_exists('data', $payload) && is_array($payload['data']))
                    ? $payload['data']
                    : $payload;

                if (is_array($allData)) {
                    $catalogs = array_merge($catalogs, $allData);

                    // Normalizamos alias para que la vista actual siga funcionando sin cambios.
                    $catalogs['frecuencia_servicio_agua'] = $catalogs['frecuencia_agua'];
                    $catalogs['frecuencia_servicio_aseo'] = $catalogs['frecuencia_aseo'];
                    $catalogs['frecuencia_servicio_electricidad'] = $catalogs['frecuencia_electricidad'];
                    $catalogs['frecuencia_servicio_gas'] = $catalogs['frecuencia_gas'];
                    $catalogs['fuente_ingreso_familiar'] = $catalogs['fuente_ingreso'];
                }
            }
        } catch (\Exception $e) {
            // Fallback silencioso: la vista sigue funcionando con catálogos vacíos.
        }

        try {
            $institutoResponse = $this->catalogoService->catalogo('instituto');
            $institutoPayload = isset($institutoResponse['data']) && is_array($institutoResponse['data']) ? $institutoResponse['data'] : null;

            if (!empty($institutoResponse['success']) && $institutoPayload) {
                $institutoData = (isset($institutoPayload['success']) && array_key_exists('data', $institutoPayload) && is_array($institutoPayload['data']))
                    ? $institutoPayload['data']
                    : $institutoPayload;

                if (is_array($institutoData)) {
                    $catalogs['instituto'] = $institutoData;
                }
            }
        } catch (\Exception $e) {
            $catalogs['instituto'] = [];
        }

        return $catalogs;
    }

    /**
     * Vista de gestión de catálogos
     */
    public function catalogs()
    {
        $this->checkAuth();
        $this->requireSuperAdmin();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $resource = isset($_GET['resource']) ? trim((string)$_GET['resource']) : '';
        if ($resource === '') {
            $resource = 'nacionalidad';
        }

        $institutoId = isset($_GET['instituto_id']) && is_numeric($_GET['instituto_id']) ? (int)$_GET['instituto_id'] : null;
        $editId = isset($_GET['edit_id']) && is_numeric($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;

        $flash = null;
        if (!empty($_SESSION['flash_message'])) {
            $flash = [
                'type' => isset($_SESSION['flash_type']) ? (string)$_SESSION['flash_type'] : 'info',
                'message' => (string)$_SESSION['flash_message'],
                'errors' => isset($_SESSION['flash_errors']) && is_array($_SESSION['flash_errors']) ? $_SESSION['flash_errors'] : [],
            ];
        }
        unset($_SESSION['flash_type'], $_SESSION['flash_message'], $_SESSION['flash_errors']);

        $catalogosMenu = [];
        $catalogoItems = [];
        $catalogoLabel = $resource;
        $apiError = null;
        $institutos = [];
        $currentTenantScoped = false;
        $editItem = null;
        $carreraActivosMap = [];

        try {
            // Menú dinámico desde backend
            $menuResponse = $this->catalogoService->catalogos();
            $menuPayload = isset($menuResponse['data']) && is_array($menuResponse['data']) ? $menuResponse['data'] : null;
            if (!empty($menuResponse['success']) && $menuPayload) {
                $menuData = (isset($menuPayload['success']) && array_key_exists('data', $menuPayload) && is_array($menuPayload['data']))
                    ? $menuPayload['data']
                    : $menuPayload;

                if (is_array($menuData)) {
                    $catalogosMenu = $menuData;
                }
            }

            // Si el resource no existe en el menú, usamos el primero disponible
            $allowedResources = [];
            foreach ($catalogosMenu as $item) {
                if (is_array($item) && isset($item['resource'])) {
                    $allowedResources[] = (string)$item['resource'];
                }
            }

            if (!empty($allowedResources) && !in_array($resource, $allowedResources, true)) {
                $resource = $allowedResources[0];
            }

            // Label para el título
            foreach ($catalogosMenu as $item) {
                if (is_array($item) && isset($item['resource']) && (string)$item['resource'] === $resource) {
                    if (isset($item['label']) && is_string($item['label']) && trim($item['label']) !== '') {
                        $catalogoLabel = $item['label'];
                    }
                    if (!empty($item['tenant_scoped'])) {
                        $currentTenantScoped = true;
                    }
                    break;
                }
            }

            // Lista de sedes (institutos) para selector
            $instResponse = $this->catalogoService->catalogoAdmin('instituto');
            $instPayload = isset($instResponse['data']) && is_array($instResponse['data']) ? $instResponse['data'] : null;
            if (!empty($instResponse['success']) && $instPayload) {
                $instData = (isset($instPayload['success']) && array_key_exists('data', $instPayload))
                    ? $instPayload['data']
                    : $instPayload;

                if (is_array($instData)) {
                    foreach ($instData as $inst) {
                        if (is_array($inst) && !empty($inst['activo']) && isset($inst['id'])) {
                            $institutos[] = $inst;
                        }
                    }
                }
            }

            if ($currentTenantScoped && empty($institutoId) && !empty($institutos) && isset($institutos[0]['id'])) {
                $institutoId = (int)$institutos[0]['id'];
            }

            // Para Carreras: mapa de sedes activas (Instituto_Carrera) para marcar checks correctamente en el modal
            if ($resource === 'carrera') {
                $mapResp = $this->catalogoService->carreraActivos();
                $mapPayload = isset($mapResp['data']) && is_array($mapResp['data']) ? $mapResp['data'] : null;
                if (!empty($mapResp['success']) && is_array($mapPayload)) {
                    $mapData = (isset($mapPayload['success']) && array_key_exists('data', $mapPayload) && is_array($mapPayload['data']))
                        ? $mapPayload['data']
                        : $mapPayload;
                    if (is_array($mapData)) {
                        $carreraActivosMap = $mapData;
                    }
                }
            }

            // Datos del catálogo seleccionado (admin: incluye inactivos)
            $params = [];
            if ($currentTenantScoped && !empty($institutoId)) {
                $params['instituto_id'] = (int)$institutoId;
            }
            $dataResponse = $this->catalogoService->catalogoAdmin($resource, $params);
            $dataPayload = isset($dataResponse['data']) && is_array($dataResponse['data']) ? $dataResponse['data'] : null;
            if (!empty($dataResponse['success']) && $dataPayload) {
                $data = (isset($dataPayload['success']) && array_key_exists('data', $dataPayload))
                    ? $dataPayload['data']
                    : $dataPayload;

                if (is_array($data)) {
                    $catalogoItems = $data;
                }
            } else {
                $message = 'No se pudo cargar el catálogo.';
                if (is_array($dataPayload) && isset($dataPayload['message']) && is_string($dataPayload['message']) && trim($dataPayload['message']) !== '') {
                    $message = $dataPayload['message'];
                }
                $apiError = [
                    'status' => isset($dataResponse['status']) ? (int)$dataResponse['status'] : 0,
                    'message' => $message,
                ];
            }

            if (!empty($editId) && !empty($catalogoItems)) {
                foreach ($catalogoItems as $it) {
                    if (is_array($it) && isset($it['id']) && (int)$it['id'] === (int)$editId) {
                        $editItem = $it;
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $apiError = [
                'status' => 0,
                'message' => 'Error de conexión con el servidor: ' . $e->getMessage(),
            ];
        }

        $this->view('admin/catalogs', [
            'title' => 'Gestión de Catálogos | Admin',
            'current_page' => 'catalogs',
            'flash' => $flash,
            'catalogosMenu' => $catalogosMenu,
            'resource' => $resource,
            'institutos' => $institutos,
            'institutoId' => $institutoId,
            'currentTenantScoped' => $currentTenantScoped,
            'editId' => $editId,
            'editItem' => $editItem,
            'carreraActivosMap' => $carreraActivosMap,
            'catalogoLabel' => $catalogoLabel,
            'catalogoItems' => $catalogoItems,
            'apiError' => $apiError,
        ], 'admin');
    }
}
