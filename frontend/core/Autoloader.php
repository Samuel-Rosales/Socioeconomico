<?php

/**
 * Autoloader PSR-4
 * Carga automáticamente las clases del proyecto
 */
class Autoloader
{
    /**
     * Registra el autoloader
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Carga automáticamente las clases
     * 
     * @param string $class Nombre completo de la clase
     */
    private static function autoload($class)
    {
        // Directorio base del proyecto
        $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        // Mapeo de namespaces a directorios
        $prefixes = [
            'App\\Controllers\\' => $baseDir . 'app' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR,
            'App\\Models\\' => $baseDir . 'app' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR,
            'App\\Services\\' => $baseDir . 'app' . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR,
            'App\\Exceptions\\' => $baseDir . 'app' . DIRECTORY_SEPARATOR . 'exceptions' . DIRECTORY_SEPARATOR,
            'Core\\' => $baseDir . 'core' . DIRECTORY_SEPARATOR,
            'Utils\\' => $baseDir . 'utils' . DIRECTORY_SEPARATOR,
        ];

        // Buscar el archivo correspondiente
        foreach ($prefixes as $prefix => $dir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            // Obtener el nombre relativo de la clase
            $relativeClass = substr($class, $len);

            // Reemplazar separadores de namespace con separadores de directorio
            $file = $dir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            // Si el archivo existe, cargarlo
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
}
