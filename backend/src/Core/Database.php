<?php
namespace App\Core;

use PDO;
use PDOException;
use App\Core\Env;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            Env::load(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env');

            $host = Env::get('DB_HOST', 'localhost');
            $port = Env::get('DB_PORT', '3306');
            $db   = Env::get('DB_NAME', 'socioeconomico_db');
            $user = Env::get('DB_USER', 'root');
            $pass = Env::get('DB_PASSWORD', '');
            $charset = Env::get('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
            
            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$instance->exec("SET time_zone = '+00:00'");
            } catch (PDOException $e) {
                // En contexto web respondemos JSON estándar para no romper consumidores.
                if (PHP_SAPI !== 'cli') {
                    if (!headers_sent()) {
                        header('Content-Type: application/json');
                    }
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'data' => [
                            'errors' => [
                                'database' => [
                                    'No se pudo conectar a la base de datos.',
                                    $e->getMessage(),
                                ],
                            ],
                        ],
                        'message' => 'Error de conexión a la base de datos',
                    ]);
                    exit;
                }

                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}