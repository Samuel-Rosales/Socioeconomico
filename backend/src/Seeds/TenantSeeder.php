<?php

namespace App\Seeds;

use App\Core\Database;
use PDO;

class TenantSeeder
{
    /** @var PDO */
    private $db;

    public function __construct(PDO $db = null)
    {
        $this->db = $db ?: Database::getConnection();
    }

    /**
     * Lista por defecto de sedes (según frontend).
     * Nota: IUJO-BARQUISIMETO ya lo crea MainSeeder; aquí agregamos las demás.
     */
    public static function defaultInstitutos()
    {
        return [
            ['siglas' => 'IUJO-BARQUISIMETO', 'nombre' => 'IUJO Barquisimeto'],
            ['siglas' => 'IUJO-CARACAS', 'nombre' => 'IUJO Caracas'],
            ['siglas' => 'IUJO-PETARE', 'nombre' => 'IUJO Petare'],
            ['siglas' => 'IUJO-GUANARITO', 'nombre' => 'IUJO Guanarito'],
            ['siglas' => 'IUSF', 'nombre' => 'IUSF'],
        ];
    }

    /**
     * Siembra institutos adicionales y copia catálogos tenant-scoped desde un instituto “origen”.
     *
     * @param array $institutos Array de [siglas => string, nombre => string]
     * @param array $opts
     *   - source_instituto_id (int|null)
     *   - source_siglas (string|null) default IUJO-BARQUISIMETO
     *   - seed_carreras (bool) default true
     *   - seed_tipo_beca (bool) default true
     *   - dry_run (bool) default false
     * @return array resumen
     */
    public function run(array $institutos, array $opts = [])
    {
        $opts = array_merge([
            'source_instituto_id' => null,
            'source_siglas' => 'IUJO-BARQUISIMETO',
            'seed_carreras' => true,
            'seed_tipo_beca' => true,
            'dry_run' => false,
        ], $opts);

        $dryRun = !empty($opts['dry_run']);

        $sourceInstitutoId = $this->resolveSourceInstitutoId($opts);

        // Defaults (fallback si el origen no tiene data)
        $defaultCarreras = [
            'Administración de Empresas', 'Contaduría', 'Educación Especial',
            'Educación Inicial', 'Educación Integral', 'Electrotecnia',
            'Electrónica', 'Informática', 'Mecánica', 'Producción Agropecuaria',
        ];

        $defaultBecas = [
            'No posee beca',
            'Becado por la institución',
            'Becado por un ente del Estado Centralizado (Sistema Patria)',
            'Becado por un ente del Estado Descentralizado (Alcaldía o Gobernación)',
            'Becado por ente privado',
            'QG',
        ];

        $sourceCarreraIds = [];
        $sourceBecas = [];

        if (!empty($sourceInstitutoId)) {
            if (!empty($opts['seed_carreras'])) {
                $sourceCarreraIds = $this->getCarreraIdsByInstituto((int)$sourceInstitutoId);
            }
            if (!empty($opts['seed_tipo_beca'])) {
                $sourceBecas = $this->getTipoBecaNombresByInstituto((int)$sourceInstitutoId);
            }
        }

        $summary = [
            'source_instituto_id' => $sourceInstitutoId,
            'dry_run' => $dryRun,
            'institutos' => [],
        ];

        foreach ($institutos as $inst) {
            $siglas = isset($inst['siglas']) ? trim((string)$inst['siglas']) : '';
            $nombre = isset($inst['nombre']) ? trim((string)$inst['nombre']) : '';

            if ($siglas === '' || $nombre === '') {
                $summary['institutos'][] = [
                    'siglas' => $siglas,
                    'nombre' => $nombre,
                    'success' => false,
                    'message' => 'Formato inválido: se requiere siglas y nombre.',
                ];
                continue;
            }

            $this->db->beginTransaction();
            try {
                $institutoId = $this->ensureInstituto($siglas, $nombre, $dryRun);

                $seededCarreras = 0;
                $seededBecas = 0;

                if (!empty($opts['seed_carreras'])) {
                    if (!empty($sourceCarreraIds)) {
                        $seededCarreras = $this->copyInstitutoCarreras((int)$institutoId, $sourceCarreraIds, $dryRun);
                    } else {
                        $seededCarreras = $this->ensureInstitutoCarrerasByNames((int)$institutoId, $defaultCarreras, $dryRun);
                    }
                }

                if (!empty($opts['seed_tipo_beca'])) {
                    $becas = !empty($sourceBecas) ? $sourceBecas : $defaultBecas;
                    $seededBecas = $this->ensureTipoBecaLocal((int)$institutoId, $becas, $dryRun);
                }

                if ($dryRun) {
                    $this->db->rollBack();
                } else {
                    $this->db->commit();
                }

                $summary['institutos'][] = [
                    'siglas' => $siglas,
                    'nombre' => $nombre,
                    'instituto_id' => (int)$institutoId,
                    'success' => true,
                    'seeded_carreras' => (int)$seededCarreras,
                    'seeded_tipo_beca' => (int)$seededBecas,
                ];
            } catch (\Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                $summary['institutos'][] = [
                    'siglas' => $siglas,
                    'nombre' => $nombre,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $summary;
    }

    private function resolveSourceInstitutoId(array $opts)
    {
        if (isset($opts['source_instituto_id']) && is_numeric($opts['source_instituto_id']) && (int)$opts['source_instituto_id'] > 0) {
            return (int)$opts['source_instituto_id'];
        }

        $siglas = isset($opts['source_siglas']) ? (string)$opts['source_siglas'] : '';
        $siglas = trim($siglas);

        if ($siglas !== '') {
            $id = $this->getInstitutoIdBySiglas($siglas);
            if (!empty($id)) {
                return (int)$id;
            }
        }

        // Fallback: primer instituto activo
        $stmt = $this->db->query("SELECT id FROM Instituto WHERE activo = 1 ORDER BY id ASC LIMIT 1");
        $id = $stmt ? $stmt->fetchColumn() : null;
        return $id ? (int)$id : null;
    }

    private function getInstitutoIdBySiglas($siglas)
    {
        $stmt = $this->db->prepare('SELECT id FROM Instituto WHERE siglas = ? LIMIT 1');
        $stmt->execute([(string)$siglas]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    private function ensureInstituto($siglas, $nombre, $dryRun)
    {
        $existingId = $this->getInstitutoIdBySiglas($siglas);
        if (!empty($existingId)) {
            if (!$dryRun) {
                $stmt = $this->db->prepare('UPDATE Instituto SET nombre = ?, activo = 1 WHERE id = ?');
                $stmt->execute([(string)$nombre, (int)$existingId]);
            }
            return (int)$existingId;
        }

        if (!$dryRun) {
            $stmt = $this->db->prepare('INSERT INTO Instituto (siglas, nombre, activo) VALUES (?, ?, 1)');
            $stmt->execute([(string)$siglas, (string)$nombre]);
        }

        // En dry-run devolvemos un id “virtual” solo para el resumen
        if ($dryRun) {
            return 0;
        }

        $id = $this->getInstitutoIdBySiglas($siglas);
        if (empty($id)) {
            throw new \RuntimeException('No se pudo crear o resolver el instituto: ' . $siglas);
        }

        return (int)$id;
    }

    private function getCarreraIdsByInstituto($institutoId)
    {
        $stmt = $this->db->prepare('SELECT carrera_id FROM Instituto_Carrera WHERE instituto_id = ? AND activo = 1');
        $stmt->execute([(int)$institutoId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ids = [];
        foreach ($rows as $r) {
            $ids[] = (int)$r['carrera_id'];
        }
        return $ids;
    }

    private function copyInstitutoCarreras($targetInstitutoId, array $carreraIds, $dryRun)
    {
        if ($dryRun) {
            return count($carreraIds);
        }

        $stmt = $this->db->prepare('INSERT IGNORE INTO Instituto_Carrera (instituto_id, carrera_id, activo) VALUES (?, ?, 1)');
        $count = 0;
        foreach ($carreraIds as $cid) {
            $stmt->execute([(int)$targetInstitutoId, (int)$cid]);
            $count++;
        }
        return $count;
    }

    private function ensureInstitutoCarrerasByNames($institutoId, array $carreras, $dryRun)
    {
        $count = 0;

        foreach ($carreras as $nombre) {
            $nombre = trim((string)$nombre);
            if ($nombre === '') {
                continue;
            }

            $carreraId = $this->ensureCarrera($nombre, $dryRun);
            if ($dryRun) {
                $count++;
                continue;
            }

            $stmt = $this->db->prepare('INSERT IGNORE INTO Instituto_Carrera (instituto_id, carrera_id, activo) VALUES (?, ?, 1)');
            $stmt->execute([(int)$institutoId, (int)$carreraId]);
            $count++;
        }

        return $count;
    }

    private function ensureCarrera($nombre, $dryRun)
    {
        // Carrera.nombre es UNIQUE, así que IGNORE es idempotente.
        if (!$dryRun) {
            $stmt = $this->db->prepare('INSERT IGNORE INTO Carrera (nombre) VALUES (?)');
            $stmt->execute([(string)$nombre]);
        }

        // En dry-run, no hay id real.
        if ($dryRun) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT id FROM Carrera WHERE nombre = ? LIMIT 1');
        $stmt->execute([(string)$nombre]);
        $id = $stmt->fetchColumn();

        if (empty($id)) {
            throw new \RuntimeException('No se pudo crear o resolver Carrera: ' . $nombre);
        }

        return (int)$id;
    }

    private function getTipoBecaNombresByInstituto($institutoId)
    {
        $stmt = $this->db->prepare('SELECT nombre FROM TipoBeca WHERE instituto_id = ? AND activo = 1');
        $stmt->execute([(int)$institutoId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $names = [];
        foreach ($rows as $r) {
            $n = isset($r['nombre']) ? trim((string)$r['nombre']) : '';
            if ($n !== '') {
                $names[] = $n;
            }
        }

        return $names;
    }

    private function ensureTipoBecaLocal($institutoId, array $becas, $dryRun)
    {
        $count = 0;

        foreach ($becas as $beca) {
            $beca = trim((string)$beca);
            if ($beca === '') {
                continue;
            }

            if ($dryRun) {
                $count++;
                continue;
            }

            // TipoBeca NO tiene índice UNIQUE, así que evitamos duplicados manualmente.
            $stmtSel = $this->db->prepare('SELECT id FROM TipoBeca WHERE instituto_id = ? AND nombre = ? LIMIT 1');
            $stmtSel->execute([(int)$institutoId, (string)$beca]);
            $exists = $stmtSel->fetchColumn();

            if (!empty($exists)) {
                $count++;
                continue;
            }

            $stmtIns = $this->db->prepare('INSERT INTO TipoBeca (nombre, instituto_id, activo) VALUES (?, ?, 1)');
            $stmtIns->execute([(string)$beca, (int)$institutoId]);
            $count++;
        }

        return $count;
    }
}
