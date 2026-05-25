<?php
// public/index.php

// Headers CORS para permitir peticiones desde el frontend
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Instituto-Id, X-Tenant-Id, X-Instituto-Siglas, X-Tenant-Code');
header('Content-Type: application/json; charset=UTF-8');

// Manejar preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 1. Cargamos el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Importamos las clases que usaremos
use App\Core\Router;
use App\Core\Env;

Env::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

$router = new Router();

// 4. Definimos las rutas

// Ruta dinámica para catálogos (GET)
$router->get('/catalogo/:resource', 'App\Controllers\CatalogController@index');

// Lista de catálogos (para construir menú en frontend)
$router->get('/catalogo', 'App\Controllers\CatalogController@resources');

// CRUD admin de catálogos (solo SUPER_ADMIN)
$router->get('/catalogo-admin/carrera/activos', 'App\\Controllers\\CatalogController@adminCarreraActivos');
$router->get('/catalogo-admin/:resource', 'App\Controllers\CatalogController@adminIndex');
$router->post('/catalogo-admin/:resource', 'App\Controllers\CatalogController@adminStore');
$router->put('/catalogo-admin/:resource/:id', 'App\Controllers\CatalogController@adminUpdate');
$router->delete('/catalogo-admin/:resource/:id', 'App\Controllers\CatalogController@adminDestroy');
$router->post('/catalogo-admin/:resource/:id/restore', 'App\Controllers\CatalogController@adminRestore');

// Ruta para registrar encuesta (POST)
$router->post('/encuesta', 'App\Controllers\EncuestaController@registrar');

// Ruta para validar duplicados (GET) - sin auth (para estudiantes sin usuario)
$router->get('/encuesta/check', 'App\Controllers\EncuestaController@checkDuplicados');

// Ruta para listar encuestas (GET) - resumen + estrato
$router->get('/encuesta', 'App\Controllers\EncuestaController@index');

// Ruta para detalle de encuesta (GET) - detalle completo
$router->get('/encuesta/:id', 'App\Controllers\EncuestaController@show');
$router->put('/encuesta/:id', 'App\Controllers\EncuestaController@update');

// Reportes (GET) - modulo desacoplado por vista
$router->get('/reportes/dashboard-general', 'App\Controllers\ReportesController@dashboardGeneral');
$router->get('/reportes/analisis-academico', 'App\Controllers\ReportesController@analisisAcademico');
$router->get('/reportes/demografico-vulnerabilidad', 'App\Controllers\ReportesController@demograficoVulnerabilidad');
$router->get('/reportes/filtros', 'App\Controllers\ReportesController@filtros');

// Auth
$router->post('/login', 'App\Controllers\AuthController@login');

// Usuarios (CRUD)
$router->get('/usuario', 'App\Controllers\UsuarioController@index');
$router->get('/usuario/:id', 'App\Controllers\UsuarioController@show');
$router->post('/usuario', 'App\Controllers\UsuarioController@store');
$router->put('/usuario/:id', 'App\Controllers\UsuarioController@update');
$router->delete('/usuario/:id', 'App\Controllers\UsuarioController@destroy');

// Alias /usuarios (compatibilidad con documentación)
$router->get('/usuarios', 'App\\Controllers\\UsuarioController@index');
$router->get('/usuarios/:id', 'App\\Controllers\\UsuarioController@show');
$router->post('/usuarios', 'App\\Controllers\\UsuarioController@store');
$router->put('/usuarios/:id', 'App\\Controllers\\UsuarioController@update');
$router->delete('/usuarios/:id', 'App\\Controllers\\UsuarioController@destroy');

// 5. Ejecutamos el router
$router->run();
