<?php

namespace Core;

/**
 * Controller - Clase base para todos los controladores
 */
class Controller
{
    protected function closeSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    protected function ensureSessionStarted()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function clearAuthSession()
    {
        $this->ensureSessionStarted();
        unset($_SESSION['auth_user']);
        unset($_SESSION['auth_token']);
        unset($_SESSION['last_activity']);
    }

    protected function isSessionTokenExpired($token)
    {
        if (!is_string($token) || trim($token) === '') {
            return true;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return true;
        }

        $payloadB64 = strtr($parts[1], '-_', '+/');
        $remainder = strlen($payloadB64) % 4;
        if ($remainder > 0) {
            $payloadB64 .= str_repeat('=', 4 - $remainder);
        }

        $payloadJson = base64_decode($payloadB64, true);
        if ($payloadJson === false) {
            return true;
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            return true;
        }

        $exp = isset($payload['exp']) ? (int)$payload['exp'] : 0;
        if ($exp <= 0) {
            return true;
        }

        return time() >= $exp;
    }

    protected function hasValidAuthSession()
    {
        $this->ensureSessionStarted();

        if (empty($_SESSION['auth_user']) || empty($_SESSION['auth_token'])) {
            return false;
        }

        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
                $this->clearAuthSession();
                $_SESSION['login_error'] = 'Tu sesión expiró por inactividad. Inicia sesión nuevamente.';
                $this->closeSession();
                return false;
            }
        }

        if ($this->isSessionTokenExpired((string)$_SESSION['auth_token'])) {
            $this->clearAuthSession();
            $_SESSION['login_error'] = 'Tu sesión expiró. Inicia sesión nuevamente.';
            $this->closeSession();
            return false;
        }

        $this->closeSession();
        return true;
    }

    protected function updateLastActivity()
    {
        $this->ensureSessionStarted();
        $_SESSION['last_activity'] = time();
        $this->closeSession();
    }

    /**
     * Carga una vista
     * 
     * @param string $view Nombre de la vista (ej: 'form/index')
     * @param array $data Datos a pasar a la vista
     * @param string $layout Layout a usar (por defecto 'main')
     */
    protected function view($view, $data = [], $layout = 'main')
    {
        // Base path / assets path (works with both /index.php and /public/index.php)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if (!isset($data['assetBase'])) {
            $data['assetBase'] = $basePath . '/public/assets';
        }

        // Extraer datos para que estén disponibles en la vista
        extract($data);

        // Iniciar buffer de salida
        ob_start();

        // Cargar la vista
        $viewPath = dirname(__DIR__) . "/app/views/{$view}.php";

        if (!file_exists($viewPath)) {
            throw new \Exception("Vista {$view} no encontrada");
        }

        require $viewPath;

        // Obtener el contenido de la vista
        $content = ob_get_clean();

        // Si hay layout, cargarlo
        if ($layout) {
            $layoutPath = dirname(__DIR__) . "/app/views/layouts/{$layout}.php";

            if (!file_exists($layoutPath)) {
                throw new \Exception("Layout {$layout} no encontrado");
            }

            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Retorna una respuesta JSON
     * 
     * @param mixed $data Datos a retornar
     * @param int $statusCode Código de estado HTTP
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirige a otra URL
     * 
     * @param string $url URL de destino
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Obtiene un parámetro POST
     * 
     * @param string $key Clave del parámetro
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function post($key, $default = null)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Obtiene un parámetro GET
     * 
     * @param string $key Clave del parámetro
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Verifica si la petición es POST
     * 
     * @return bool
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Verifica si la petición es GET
     * 
     * @return bool
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}
