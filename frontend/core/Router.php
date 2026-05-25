<?php

namespace Core;

/**
 * Router - Sistema de enrutamiento simple
 */
class Router
{
    private $routes = [];
    private $notFoundCallback;

    /**
     * Registra una ruta GET
     * 
     * @param string $path Ruta URL
     * @param string $controller Controlador en formato "Controller@method"
     */
    public function get($path, $controller)
    {
        $this->addRoute('GET', $path, $controller);
    }

    /**
     * Registra una ruta POST
     * 
     * @param string $path Ruta URL
     * @param string $controller Controlador en formato "Controller@method"
     */
    public function post($path, $controller)
    {
        $this->addRoute('POST', $path, $controller);
    }

    /**
     * Agrega una ruta al sistema
     * 
     * @param string $method Método HTTP
     * @param string $path Ruta URL
     * @param string $controller Controlador
     */
    private function addRoute($method, $path, $controller)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller
        ];
    }

    /**
     * Define el callback para rutas no encontradas (404)
     * 
     * @param callable $callback Función a ejecutar
     */
    public function notFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    /**
     * Ejecuta el router y procesa la petición actual
     */
    public function run()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remover el directorio base si existe
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $requestUri = str_replace($scriptName, '', $requestUri);
        }

        // Asegurar que empiece con /
        $requestUri = '/' . ltrim($requestUri, '/');

        // Buscar la ruta correspondiente
        foreach ($this->routes as $route) {
            $params = [];
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri, $params)) {
                $this->dispatch($route['controller'], $params);
                return;
            }
        }

        // Si no se encontró la ruta, ejecutar callback 404
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
        } else {
            http_response_code(404);
            echo "404 - Página no encontrada";
        }
    }

    /**
     * Verifica si la ruta coincide con el patrón
     * 
     * @param string $pattern Patrón de la ruta
     * @param string $path Ruta actual
     * @param array &$params Parámetros extraídos
     * @return bool
     */
    private function matchPath($pattern, $path, &$params = [])
    {
        // Convertir el patrón con parámetros :param a una expresión regular
        $regex = preg_replace('/\:([a-zA-Z0-9_]+)/', '(?P<$1>[a-zA-Z0-9_\-]+)', $pattern);
        
        // Escapar barras
        $regex = str_replace('/', '\/', $regex);
        
        // Patrón final
        $regex = '/^' . $regex . '$/';

        if (preg_match($regex, $path, $matches)) {
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Despacha la petición al controlador correspondiente
     * 
     * @param string $controller Controlador en formato "Controller@method"
     * @param array $params Parámetros extraídos de la URL
     */
    private function dispatch($controller, $params = [])
    {
        list($controllerName, $method) = explode('@', $controller);

        // Construir el nombre completo de la clase
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controlador {$controllerClass} no encontrado");
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $method)) {
            throw new \Exception("Método {$method} no encontrado en {$controllerClass}");
        }

        // Ejecutar el método del controlador con los parámetros
        call_user_func_array([$controllerInstance, $method], array_values($params));
    }
}
