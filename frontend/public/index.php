<?php
/**
 * Front Controller - Punto de entrada único de la aplicación
 */

// Cargar autoloader
require_once __DIR__ . '/../core/Autoloader.php';
Autoloader::register();

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Cargar y ejecutar router
$router = require_once __DIR__ . '/../config/routes.php';
$router->run();
