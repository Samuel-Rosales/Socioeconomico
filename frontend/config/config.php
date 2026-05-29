<?php

/**
 * Archivo de configuración general
 */

// Rutas del proyecto (definidas primero para poder cargar .env)
define('ROOT_PATH', dirname(__DIR__));

// Cargar variables de entorno desde .env
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = array_map('trim', explode('=', $line, 2));
        $key = strtoupper($key);
        if (!getenv($key)) {
            putenv("{$key}={$value}");
        }
    }
}

// Configuración de errores (desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de zona horaria
date_default_timezone_set('America/Caracas');

// Resto de rutas
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Base URL para redirecciones y assets (auto-detecta dominio si no está en .env)
function _detectBaseUrl(): string
{
    $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $publicPos = strpos($script, '/public/');
    $basePath = $publicPos !== false ? substr($script, 0, $publicPos) : rtrim(dirname($script), '/');
    return $proto . '://' . $host . $basePath;
}
define('BASE_URL', getenv('BASE_URL') ?: _detectBaseUrl());

// Configuración de la aplicación
define('APP_NAME', getenv('APP_NAME') ?: 'Formulario Socioeconómico');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Configuración de API
define('API_BASE_URL', getenv('API_BASE_URL') ?: 'http://localhost:8081/');
define('API_TOKEN', getenv('API_TOKEN') ?: '');
define('API_TIMEOUT', (int)(getenv('API_TIMEOUT')) ?: 30);

// Configuración de sesión
define('SESSION_TIMEOUT', (int)(getenv('SESSION_TIMEOUT')) ?: 300);
define('SESSION_WARNING_BEFORE', (int)(getenv('SESSION_WARNING_BEFORE')) ?: 60);

// Multi-tenant (opcional)
$envInstitutoId = getenv('INSTITUTO_ID');
define('INSTITUTO_ID', $envInstitutoId !== false && is_numeric($envInstitutoId) ? (int)$envInstitutoId : null);

// Multi-sede (opcional)
define('SEDE_INSTITUTO_MAP', getenv('SEDE_INSTITUTO_MAP') ? json_decode(getenv('SEDE_INSTITUTO_MAP'), true) : []);

// Otras constantes
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost');
define('SITE_NAME', getenv('SITE_NAME') ?: 'Formulario Socioeconómico');

/**
 * Convierte una fecha/hora UTC a America/Caracas y la formatea para mostrar.
 * Si el valor ya está formateado (contiene texto no numérico), lo devuelve tal cual.
 */
function formatFechaUTC($value, $format = 'd M Y, h:i A')
{
    if (!is_string($value) || trim($value) === '') {
        return $value;
    }

    try {
        $dt = new DateTime($value, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('America/Caracas'));
        return $dt->format($format);
    } catch (Exception $e) {
        return $value;
    }
}
