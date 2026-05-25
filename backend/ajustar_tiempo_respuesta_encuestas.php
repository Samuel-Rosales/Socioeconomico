<?php

/**
 * Ajusta el campo Encuesta.inicio para que siempre sea menor que Encuesta.creado,
 * restando un intervalo aleatorio solo en minutos.
 *
 * Uso:
 *   php ajustar_tiempo_respuesta_encuestas.php
 *   php ajustar_tiempo_respuesta_encuestas.php --apply
 *   php ajustar_tiempo_respuesta_encuestas.php --apply --instituto-id=1
 *   php ajustar_tiempo_respuesta_encuestas.php --apply --from=2026-01-01 --to=2026-12-31
 *   php ajustar_tiempo_respuesta_encuestas.php --apply --min-minutes=10 --max-minutes=30
 *
 * Por seguridad, si no se pasa --apply solo muestra un preview.
 */

require __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

$options = getopt('', [
    'apply',
    'instituto-id::',
    'from::',
    'to::',
    'min-minutes::',
    'max-minutes::',
    'help',
]);

if (isset($options['help'])) {
    echo "Uso:\n";
    echo "  php ajustar_tiempo_respuesta_encuestas.php [--apply] [--instituto-id=ID] [--from=YYYY-MM-DD] [--to=YYYY-MM-DD]\n";
    echo "      [--min-minutes=10 --max-minutes=30]\n";
    exit(0);
}

$apply = isset($options['apply']);
$minMinutes = isset($options['min-minutes']) ? (int)$options['min-minutes'] : 10;
$maxMinutes = isset($options['max-minutes']) ? (int)$options['max-minutes'] : 30;
$institutoId = isset($options['instituto-id']) && is_numeric($options['instituto-id']) ? (int)$options['instituto-id'] : null;
$from = isset($options['from']) ? trim((string)$options['from']) : '';
$to = isset($options['to']) ? trim((string)$options['to']) : '';

if ($minMinutes < 0 || $maxMinutes < 0 || $maxMinutes < $minMinutes) {
    fwrite(STDERR, "Rango inválido: usa min-minutes <= max-minutes y ambos >= 0\n");
    exit(1);
}

if ($maxMinutes === 0) {
    fwrite(STDERR, "Rango inválido: max-minutes debe ser mayor a 0 para garantizar inicio < creado\n");
    exit(1);
}

$where = [
    'e.creado IS NOT NULL',
    'e.creado <> "0000-00-00 00:00:00"',
];
$bindings = [];

if ($institutoId !== null && $institutoId > 0) {
    $where[] = 'e.instituto_id = :instituto_id';
    $bindings['instituto_id'] = $institutoId;
}

if ($from !== '') {
    $where[] = 'DATE(e.creado) >= :from_date';
    $bindings['from_date'] = $from;
}

if ($to !== '') {
    $where[] = 'DATE(e.creado) <= :to_date';
    $bindings['to_date'] = $to;
}

$whereSql = implode(' AND ', $where);
$minutesRange = ($maxMinutes - $minMinutes) + 1;

$pdo = Database::getConnection();

$countSql = "SELECT COUNT(*) AS total FROM Encuesta e WHERE $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($bindings);
$total = (int)$countStmt->fetchColumn();

echo "\n=== Ajuste de inicio respecto a creado ===\n";
echo "Rango minutes: {$minMinutes}..{$maxMinutes}\n";
if ($institutoId !== null) {
    echo "Filtro instituto_id: {$institutoId}\n";
}
if ($from !== '' || $to !== '') {
    echo "Filtro fechas creado: {$from} .. {$to}\n";
}
echo "Registros elegibles: {$total}\n";

if ($total === 0) {
    echo "No hay registros para actualizar.\n";
    exit(0);
}

$avgSql = "SELECT
    ROUND(AVG(TIMESTAMPDIFF(SECOND, e.inicio, e.creado) / 60), 2) AS avg_minutes,
    MIN(TIMESTAMPDIFF(MINUTE, e.inicio, e.creado)) AS min_minutes,
    MAX(TIMESTAMPDIFF(MINUTE, e.inicio, e.creado)) AS max_minutes
FROM Encuesta e
WHERE $whereSql
    AND e.inicio IS NOT NULL
    AND e.creado >= e.inicio";

$avgStmt = $pdo->prepare($avgSql);
$avgStmt->execute($bindings);
$before = $avgStmt->fetch(PDO::FETCH_ASSOC);

echo "Antes -> promedio: " . (isset($before['avg_minutes']) ? $before['avg_minutes'] : '0')
    . " min, min: " . (isset($before['min_minutes']) ? $before['min_minutes'] : '0')
    . ", max: " . (isset($before['max_minutes']) ? $before['max_minutes'] : '0') . "\n";

if (!$apply) {
    echo "\nModo preview: no se aplicaron cambios. Usa --apply para ejecutar el UPDATE de inicio.\n";
    exit(0);
}

$updateSql = "UPDATE Encuesta e
SET e.inicio = DATE_SUB(
    e.creado,
    INTERVAL FLOOR(:min_minutes + (RAND() * :minutes_range)) MINUTE
)
WHERE $whereSql";

$updateStmt = $pdo->prepare($updateSql);
$updateStmt->bindValue(':min_minutes', $minMinutes, PDO::PARAM_INT);
$updateStmt->bindValue(':minutes_range', $minutesRange, PDO::PARAM_INT);

foreach ($bindings as $key => $value) {
    if (is_int($value)) {
        $updateStmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
    } else {
        $updateStmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }
}

$pdo->beginTransaction();
try {
    $updateStmt->execute();
    $updated = $updateStmt->rowCount();
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Error al actualizar: " . $e->getMessage() . "\n");
    exit(1);
}

$avgStmt = $pdo->prepare($avgSql);
$avgStmt->execute($bindings);
$after = $avgStmt->fetch(PDO::FETCH_ASSOC);

echo "Actualizados: {$updated}\n";
echo "Después -> promedio: " . (isset($after['avg_minutes']) ? $after['avg_minutes'] : '0')
    . " min, min: " . (isset($after['min_minutes']) ? $after['min_minutes'] : '0')
    . ", max: " . (isset($after['max_minutes']) ? $after['max_minutes'] : '0') . "\n";

$invalidSql = "SELECT COUNT(*) FROM Encuesta e WHERE $whereSql AND e.inicio > e.creado";
$invalidStmt = $pdo->prepare($invalidSql);
$invalidStmt->execute($bindings);
$invalid = (int)$invalidStmt->fetchColumn();

echo "Registros con inicio > creado (debería ser 0): {$invalid}\n";

echo "\nListo.\n";
