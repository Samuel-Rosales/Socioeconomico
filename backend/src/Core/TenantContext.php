<?php

namespace App\Core;

use App\Core\Database;

class TenantContext
{
    /**
     * Resuelve el instituto (tenant) actual.
     *
     * Orden de prioridad:
     * - Header: X-Instituto-Id / X-Tenant-Id
     * - Query: ?instituto_id=
     * - Body/POST: instituto_id
    * - Fallback (opcional): primer Instituto activo (solo compatibilidad; no recomendado en endpoints públicos)
     */
    public static function resolveInstitutoId(array $requestData = null, $allowFallback = true)
    {
        $headerInstituto = $_SERVER['HTTP_X_INSTITUTO_ID'] ?? null;
        $headerTenant = $_SERVER['HTTP_X_TENANT_ID'] ?? null;
        $headerInstitutoSiglas = $_SERVER['HTTP_X_INSTITUTO_SIGLAS'] ?? null;
        $headerTenantCode = $_SERVER['HTTP_X_TENANT_CODE'] ?? null;
        $queryInstituto = $_GET['instituto_id'] ?? null;
        $queryInstitutoSiglas = $_GET['instituto_siglas'] ?? ($_GET['sede'] ?? null);
        $bodyInstituto = null;
        $bodyInstitutoSiglas = null;

        if (is_array($requestData)) {
            if (isset($requestData['instituto_id'])) {
                $bodyInstituto = $requestData['instituto_id'];
            }
            if (isset($requestData['instituto_siglas'])) {
                $bodyInstitutoSiglas = $requestData['instituto_siglas'];
            } elseif (isset($requestData['sede'])) {
                $bodyInstitutoSiglas = $requestData['sede'];
            }
        } else {
            if (isset($_POST['instituto_id'])) {
                $bodyInstituto = $_POST['instituto_id'];
            }
            if (isset($_POST['instituto_siglas'])) {
                $bodyInstitutoSiglas = $_POST['instituto_siglas'];
            } elseif (isset($_POST['sede'])) {
                $bodyInstitutoSiglas = $_POST['sede'];
            }
        }

        foreach ([$headerInstituto, $headerTenant, $queryInstituto, $bodyInstituto] as $candidate) {
            if (is_numeric($candidate) && (int)$candidate > 0) {
                return (int)$candidate;
            }
        }

        foreach ([$headerInstitutoSiglas, $headerTenantCode, $queryInstitutoSiglas, $bodyInstitutoSiglas] as $candidate) {
            if (is_string($candidate)) {
                $siglas = trim($candidate);
                if ($siglas !== '') {
                    $id = self::findInstitutoIdBySiglas($siglas);
                    if ($id !== null) {
                        return $id;
                    }
                }
            }
        }

        if (!$allowFallback) {
            return null;
        }

        // Fallback: primer Instituto activo (mantiene compatibilidad con frontend actual)
        $db = Database::getConnection();
        $stmt = $db->query("SELECT id FROM Instituto WHERE activo = 1 ORDER BY id ASC LIMIT 1");
        $id = $stmt ? $stmt->fetchColumn() : null;

        return $id ? (int)$id : null;
    }

    private static function findInstitutoIdBySiglas($siglas)
    {
        $siglas = trim((string)$siglas);
        if ($siglas === '') {
            return null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM Instituto WHERE activo = 1 AND LOWER(siglas) = LOWER(:siglas) LIMIT 1');
        $stmt->execute(['siglas' => $siglas]);
        $id = $stmt->fetchColumn();

        return $id ? (int)$id : null;
    }
}
