<?php

// CLI: Importación masiva de encuestas desde un CSV.
// Compatible con PHP 7.1.x (XAMPP).
//
// Ejemplos:
//  php import_encuestas.php --file="data/encuestas.csv" --delimiter="," --dry-run
//  php import_encuestas.php --file="data/encuestas.csv" --delimiter=";" --default-instituto-id=1
//  php import_encuestas.php --file="data/encuestas.csv" --map="import_map.json" --report="import_report.json"

require_once __DIR__ . '/vendor/autoload.php';

use App\Import\EncuestaCsvImporter;

function printUsage()
{
    $msg = "Uso:\n";
    $msg .= "  php import_encuestas.php --file=PATH [--delimiter=,] [--encoding=UTF-8] [--map=PATH] [--report=PATH]\n";
    $msg .= "                         [--default-instituto-id=ID] [--batch-size=200] [--dry-run] [--stop-on-error]\n";
    $msg .= "                         [--strict-fks=1|0] [--strict-validation=1|0] [--on-duplicate=skip|error]\n\n";

    $msg .= "Notas:\n";
    $msg .= "- Por defecto el script asume que el CSV tiene headers que coinciden con columnas de la tabla Encuesta\n";
    $msg .= "  (o aliases comunes del frontend). Si no, usa --map con un JSON {\"Header CSV\":\"columna_bd\"}.\n";
    $msg .= "- Para columnas *_id puedes pasar IDs numéricos o el nombre (se resuelve contra tablas catálogo).\n";
    $msg .= "- Relaciones opcionales: columnas 'activos', 'servicios', 'ambientes' (valores separados por | , o ;).\n";

    echo $msg;
}

$options = getopt('', [
    'file:',
    'delimiter::',
    'encoding::',
    'map::',
    'report::',
    'default-instituto-id::',
    'batch-size::',
    'dry-run',
    'stop-on-error',
    'strict-fks::',
    'strict-validation::',
    'on-duplicate::',
    'help',
]);

if (isset($options['help']) || !isset($options['file'])) {
    printUsage();
    exit(isset($options['help']) ? 0 : 2);
}

$filePath = $options['file'];

$delimiter = isset($options['delimiter']) ? (string)$options['delimiter'] : ',';
$encoding = isset($options['encoding']) ? (string)$options['encoding'] : 'UTF-8';
$map = isset($options['map']) ? (string)$options['map'] : null;

$reportPath = isset($options['report']) ? (string)$options['report'] : null;
if ($reportPath === null || trim($reportPath) === '') {
    $reportPath = __DIR__ . '/import_report_' . date('Ymd_His') . '.json';
}

$defaultInstitutoId = isset($options['default-instituto-id']) && $options['default-instituto-id'] !== ''
    ? (int)$options['default-instituto-id']
    : null;

$batchSize = isset($options['batch-size']) && $options['batch-size'] !== ''
    ? (int)$options['batch-size']
    : 200;

$dryRun = isset($options['dry-run']);
$stopOnError = isset($options['stop-on-error']);

$strictFks = true;
if (isset($options['strict-fks'])) {
    $strictFks = ((string)$options['strict-fks'] !== '0');
}

$strictValidation = true;
if (isset($options['strict-validation'])) {
    $strictValidation = ((string)$options['strict-validation'] !== '0');
}

$onDuplicate = isset($options['on-duplicate']) ? (string)$options['on-duplicate'] : 'skip';
if (!in_array($onDuplicate, ['skip', 'error'], true)) {
    echo "--on-duplicate inválido. Usa skip|error\n";
    exit(2);
}

try {
    $importer = new EncuestaCsvImporter();

    $startedAt = date('c');

    $result = $importer->import($filePath, [
        'delimiter' => $delimiter,
        'encoding' => $encoding,
        'map' => $map,
        'default_instituto_id' => $defaultInstitutoId,
        'batch_size' => $batchSize,
        'dry_run' => $dryRun,
        'stop_on_error' => $stopOnError,
        'strict_fks' => $strictFks,
        'strict_validation' => $strictValidation,
        'on_duplicate' => $onDuplicate,
    ]);

    $finishedAt = date('c');

    $report = [
        'started_at' => $startedAt,
        'finished_at' => $finishedAt,
        'file' => $filePath,
        'options' => [
            'delimiter' => $delimiter,
            'encoding' => $encoding,
            'map' => $map,
            'default_instituto_id' => $defaultInstitutoId,
            'batch_size' => $batchSize,
            'dry_run' => $dryRun,
            'stop_on_error' => $stopOnError,
            'strict_fks' => $strictFks,
            'strict_validation' => $strictValidation,
            'on_duplicate' => $onDuplicate,
        ],
        'result' => $result,
    ];

    file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Output consola
    echo "\n";
    echo $result['message'] . "\n";

    if (isset($result['summary'])) {
        echo "Resumen:\n";
        foreach ($result['summary'] as $k => $v) {
            echo "- $k: $v\n";
        }
    }

    if (!empty($result['extra_error_count'])) {
        echo "\nNota: Se omitieron " . (int)$result['extra_error_count'] . " filas con error del reporte por límite.\n";
    }

    echo "\nReporte: $reportPath\n";

    exit(!empty($result['success']) ? 0 : 1);
} catch (Exception $e) {
    echo "Error fatal: " . $e->getMessage() . "\n";
    exit(2);
}
