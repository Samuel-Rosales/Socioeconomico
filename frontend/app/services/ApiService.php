<?php

namespace App\Services;

/**
 * ApiService - Servicio base para consumir APIs externas
 */
class ApiService
{
    private $baseUrl;
    private $headers = [];
    private $timeout = 30;

    /**
     * Constructor
     * 
     * @param string $baseUrl URL base de la API
     */
    public function __construct($baseUrl = null)
    {
        $this->baseUrl = $baseUrl ?: API_BASE_URL;

        // Multi-tenant (opcional)
        if (defined('INSTITUTO_ID') && !empty(INSTITUTO_ID)) {
            $this->setHeader('X-Instituto-Id', (string) INSTITUTO_ID);
        }
    }

    /**
     * Establece un header personalizado
     * 
     * @param string $key Nombre del header
     * @param string $value Valor del header
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * Establece el timeout de las peticiones
     * 
     * @param int $seconds Segundos de timeout
     */
    public function setTimeout($seconds)
    {
        $this->timeout = $seconds;
    }

    /**
     * Realiza una petición GET
     * 
     * @param string $endpoint Endpoint de la API
     * @param array $params Parámetros query string
     * @return array Respuesta de la API
     */
    public function get($endpoint, $params = [])
    {
        $url = $this->buildUrl($endpoint, $params);
        return $this->request('GET', $url);
    }

    /**
     * Realiza una petición POST
     * 
     * @param string $endpoint Endpoint de la API
     * @param array $data Datos a enviar
     * @return array Respuesta de la API
     */
    public function post($endpoint, $data = [])
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('POST', $url, $data);
    }

    private function containsCurlFile($data)
    {
        if ($data instanceof \CURLFile) {
            return true;
        }

        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $value) {
            if ($this->containsCurlFile($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Aplana arrays anidados para que cURL los envíe correctamente en multipart/form-data
     * 
     * Convierte: ['ambientes_vivienda' => ['1', '2', '3']]
     * A: ['ambientes_vivienda[0]' => '1', 'ambientes_vivienda[1]' => '2', 'ambientes_vivienda[2]' => '3']
     */
    private function flattenArraysForMultipart(array $data): array
    {
        $flattened = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $index => $item) {
                    $flattened["{$key}[{$index}]"] = $item;
                }
            } else {
                $flattened[$key] = $value;
            }
        }
        
        return $flattened;
    }

    /**
     * Realiza una petición PUT
     * 
     * @param string $endpoint Endpoint de la API
     * @param array $data Datos a enviar
     * @return array Respuesta de la API
     */
    public function put($endpoint, $data = [])
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('PUT', $url, $data);
    }

    /**
     * Realiza una petición DELETE
     * 
     * @param string $endpoint Endpoint de la API
     * @return array Respuesta de la API
     */
    public function delete($endpoint)
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('DELETE', $url);
    }

    /**
     * Realiza una petición PATCH
     * 
     * @param string $endpoint Endpoint de la API
     * @param array $data Datos a enviar
     * @return array Respuesta de la API
     */
    public function patch($endpoint, $data = [])
    {
        $url = $this->buildUrl($endpoint);
        return $this->request('PATCH', $url, $data);
    }

    /**
     * Construye la URL completa
     * 
     * @param string $endpoint Endpoint
     * @param array $params Parámetros query string
     * @return string URL completa
     */
    private function buildUrl($endpoint, $params = [])
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Realiza la petición HTTP usando cURL
     * 
     * @param string $method Método HTTP
     * @param string $url URL completa
     * @param array $data Datos a enviar
     * @return array Respuesta parseada
     */
    private function request($method, $url, $data = null)
    {
        // Si la petición trae instituto_id explícito, debe prevalecer sobre el header global.
        // Esto permite multi-sede por selector (query/body) incluso si INSTITUTO_ID está definido.
        $institutoOverride = null;
        $parts = @parse_url($url);
        if (is_array($parts) && !empty($parts['query'])) {
            $query = [];
            parse_str($parts['query'], $query);
            if (isset($query['instituto_id']) && is_numeric($query['instituto_id']) && (int)$query['instituto_id'] > 0) {
                $institutoOverride = (int)$query['instituto_id'];
            }
        }

        if ($institutoOverride === null && is_array($data)
            && isset($data['instituto_id']) && is_numeric($data['instituto_id']) && (int)$data['instituto_id'] > 0
        ) {
            $institutoOverride = (int)$data['instituto_id'];
        }

        // Headers efectivos para ESTA petición (no mutar $this->headers)
        $effectiveHeaders = $this->headers;

        if ($institutoOverride !== null) {
            $effectiveHeaders['X-Instituto-Id'] = (string)$institutoOverride;
        }

        // Si hay token en sesión, enviar Bearer automáticamente.
        // Se lee desde $_SESSION aunque la sesión ya esté cerrada en este request.
        if (!isset($effectiveHeaders['Authorization'])
            && !empty($_SESSION['auth_token'])
        ) {
            $effectiveHeaders['Authorization'] = 'Bearer ' . (string) $_SESSION['auth_token'];
        }

        $ch = curl_init();

        // Configurar opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $isMultipart = is_array($data) && $this->containsCurlFile($data);

        // Configurar headers
        $headers = [];
        if (!$isMultipart) {
            $headers[] = 'Content-Type: application/json';
        }
        foreach ($effectiveHeaders as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Si hay datos, enviarlos como JSON o multipart según corresponda.
        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            if ($isMultipart) {
                // IMPORTANTE: cURL no maneja bien arrays anidados en multipart
                // Necesitamos aplanarlos: ['field' => ['a', 'b']] -> ['field[0]' => 'a', 'field[1]' => 'b']
                $data = $this->flattenArraysForMultipart($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        // Ejecutar petición
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \Exception("Error en petición cURL: {$error}");
        }

        if ($httpCode === 403) {

            // $logEntry = [
            //     'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            //     'context' => 'ApiService::request - 403 Forbidden',
            //     'instituto_id_header' => $effectiveHeaders['X-Instituto-Id'] ?? 'null',
            //     'authorization_header' => $effectiveHeaders['Authorization'] ?? 'null',
            //     'tenant_instituto_id' => $tenantInstitutoId ?? 'null',
            //     'httpCode' => $httpCode,
            //     'response' => $response,
            //     'error' => $error,
            // ];

            // $logLine = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . str_repeat('-', 80) . PHP_EOL;

            // file_put_contents(__DIR__ . '/debug_REQ.log', $logLine, FILE_APPEND | LOCK_EX);

            throw new \App\Exceptions\SessionExpiredException(
                'Tu sesión fue invalidada. Inicia sesión nuevamente.'
            );
        }

        $parsedResponse = json_decode($response, true);

        // Retornar respuesta con código HTTP
        return [
            'status' => $httpCode,
            'data' => $parsedResponse,
            'success' => $httpCode >= 200 && $httpCode < 300
        ];
    }
}
