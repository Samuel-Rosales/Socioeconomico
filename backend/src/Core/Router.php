<?php
namespace App\Core;

class Router {
    protected $routes = [];

    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function put($path, $handler) {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete($path, $handler) {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Ajuste para XAMPP
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $basePath = rtrim($basePath, '/');
        $path = '/' . ltrim(substr($uri, strlen($basePath)), '/');
        $path = rtrim($path, '/') ?: '/';

        if (!isset($this->routes[$method])) {
            $allowed = array_keys($this->routes);
            $this->respondError(405, 'Método no permitido', [
                'method' => ["Método '$method' no permitido."],
                'allowed_methods' => $allowed,
            ]);
            return;
        }

        // 1. Buscamos primero coincidencia estática (por velocidad)
        if (isset($this->routes[$method][$path])) {
            $this->dispatch($this->routes[$method][$path]);
            return;
        }

        // 2. Si no hay estática, buscamos rutas dinámicas (con parámetros :)
        foreach ($this->routes[$method] as $route => $handler) {
            // Convertimos la ruta /catalogo/:resource en una Regex
            if ($this->matchRoute($route, $path, $matches)) {
                // Filtramos los resultados para quedarnos solo con los parámetros nombrados
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->dispatch($handler, $params);
                return;
            }
        }

        // Si la ruta existe bajo otro método, respondemos 405
        $allowedMethods = $this->findAllowedMethodsForPath($path);
        if (!empty($allowedMethods)) {
            header('Allow: ' . implode(', ', $allowedMethods));
            $this->respondError(405, 'Método no permitido', [
                'method' => ["Método '$method' no permitido para '$path'."],
                'allowed_methods' => $allowedMethods,
            ]);
            return;
        }

        $this->respondError(404, 'Ruta no encontrada', [
            'route' => ["Ruta '$path' no encontrada."],
        ]);
    }

    private function dispatch($handler, $params = []) {
        list($class, $method) = explode('@', $handler);
        
        // Verificación de seguridad Senior: ¿Existe la clase y el método?
        if (!class_exists($class)) {
            $this->respondError(500, 'Error interno', [
                'controller' => ["La clase controlador '$class' no existe."],
            ]);
            return;
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            $this->respondError(500, 'Error interno', [
                'handler' => ["El método '$method' no existe en el controlador '$class'."],
            ]);
            return;
        }

        // Llamamos al método pasando los parámetros detectados
        try {
            $controller->$method($params);
        } catch (\Throwable $e) {
            $message = 'Error interno del servidor';
            $errors = [
                'exception' => [$e->getMessage()],
            ];

            // Caso común en entornos existentes: schema viejo sin Rol.codigo
            if ($e instanceof \PDOException && strpos($e->getMessage(), "Unknown column 'r.codigo'") !== false) {
                $message = 'Base de datos desactualizada: falta la columna Rol.codigo';
                $errors['database'] = [
                    'Actualiza el schema (script-db.sql) o agrega la columna con ALTER TABLE.',
                ];
            }

            $this->respondError(500, $message, $errors);
            return;
        }
    }

    private function respondError($status, $message, array $errors = [])
    {
        http_response_code((int)$status);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'data' => [
                'errors' => $errors,
            ],
            'message' => $message,
        ]);
    }

    private function matchRoute($route, $path, &$matches = null)
    {
        if ($route === $path) {
            $matches = [];
            return true;
        }

        if (strpos($route, ':') === false) {
            return false;
        }

        $pattern = preg_replace('/:([^\/]+)/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $path, $matches) === 1;
    }

    private function findAllowedMethodsForPath($path)
    {
        $allowed = [];

        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route => $handler) {
                if ($this->matchRoute($route, $path, $matches)) {
                    $allowed[] = $method;
                    break;
                }
            }
        }

        return $allowed;
    }
}