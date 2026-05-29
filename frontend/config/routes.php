<?php

/**
 * Definición de rutas de la aplicación
 */

use Core\Router;

$router = new Router();

// Rutas GET
$router->get('/', 'HomeController@index');
$router->get('/:sede/formulario', 'FormController@index');
$router->get('/encuesta/check', 'FormController@checkDuplicados');
$router->get('/login', 'AuthController@login');
$router->get('/success', 'FormController@success');

// Rutas POST
$router->post('/login', 'AuthController@authenticate');
$router->post('/logout', 'AuthController@logout');
$router->post('/logout/invalidate', 'AuthController@invalidate');
$router->post('/submit', 'FormController@submit');
$router->post('/:sede/formulario/submit', 'FormController@submit');
$router->post('/admin/heartbeat', 'AdminController@heartbeat');

// Rutas Admin
$router->get('/admin', 'AdminController@index');
$router->get('/admin/estadisticas', 'ReportesController@dashboardGeneral');
$router->get('/admin/reportes/dashboard-general', 'ReportesController@dashboardGeneral');
$router->get('/admin/reportes/analisis-academico', 'ReportesController@analisisAcademico');
$router->get('/admin/reportes/demografico-vulnerabilidad', 'ReportesController@demograficoVulnerabilidad');
$router->get('/admin/usuarios', 'AdminController@users');
$router->get('/admin/respuestas', 'AdminController@responses');
$router->get('/admin/respuestas/:id', 'AdminController@responseDetail');
$router->get('/admin/cedulas/:filename', 'AdminController@cedulaFile');
$router->post('/admin/respuestas/:id/update', 'AdminController@responseUpdate');
$router->get('/admin/catalogos', 'AdminController@catalogs');

// Acciones Admin Catálogos
$router->post('/admin/catalogos/create', 'AdminCatalogsController@create');
$router->post('/admin/catalogos/update/:id', 'AdminCatalogsController@update');
$router->post('/admin/catalogos/delete/:id', 'AdminCatalogsController@delete');
$router->post('/admin/catalogos/restore/:id', 'AdminCatalogsController@restore');

// Acciones Admin Usuarios
$router->post('/admin/usuarios/create', 'AdminUsersController@create');
$router->post('/admin/usuarios/update/:id', 'AdminUsersController@update');
$router->post('/admin/usuarios/delete/:id', 'AdminUsersController@delete');

// Admin - Configuración de encuestas
$router->get('/admin/configuracion-encuestas', 'AdminController@encuestaConfig');
$router->post('/admin/configuracion-encuestas/toggle', 'AdminController@encuestaConfigToggle');

// Admin - Crear encuesta
$router->get('/admin/encuestas/nueva', 'AdminController@nuevaEncuesta');
$router->post('/admin/encuestas/nueva', 'AdminController@crearEncuesta');

// Ruta 404
$router->notFound(function () {
    $controller = new App\Controllers\ErrorsController();
    $controller->show404();
});

return $router;
