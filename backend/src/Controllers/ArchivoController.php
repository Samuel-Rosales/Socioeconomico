<?php

namespace App\Controllers;

use App\Core\Auth;

class ArchivoController
{
    private $cedulasDir;

    public function __construct()
    {
        $this->cedulasDir = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'cedulas';
    }

    public function serveCedula($params = [])
    {
        Auth::requireAuth(null);

        $filename = isset($params['filename']) ? trim((string)$params['filename']) : '';
        $filename = basename($filename);

        if ($filename === '' || !preg_match('/^[A-Za-z0-9._-]+\.(jpe?g|png|webp)$/i', $filename)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Archivo inválido.',
                'data' => ['errors' => ['file' => ['Nombre de archivo inválido.']]],
            ]);
            return;
        }

        $path = $this->cedulasDir . DIRECTORY_SEPARATOR . $filename;

        if (!is_file($path)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Archivo no encontrado.',
                'data' => ['errors' => ['file' => ['El archivo solicitado no existe.']]],
            ]);
            return;
        }

        $mimeType = $this->detectMimeType($path, $filename);

        http_response_code(200);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=300');

        readfile($path);
    }

    private function detectMimeType($path, $filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];

        if (isset($map[$extension])) {
            return $map[$extension];
        }

        if (function_exists('finfo_open')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($path);
            if (is_string($detected) && trim($detected) !== '') {
                return $detected;
            }
        }

        return 'application/octet-stream';
    }
}
