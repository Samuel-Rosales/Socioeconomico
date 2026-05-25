<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Seeds\TenantSeeder;

// Opciones:
// --config=path.json            (opcional) JSON con { "institutos": [ {"siglas":"...","nombre":"..."} ] }
// --source-siglas=IUJO-BARQUISIMETO     (opcional) de dónde copiar Carrera/TipoBeca (default IUJO-BARQUISIMETO)
// --source-instituto-id=1       (opcional) alternativa por id
// --dry-run                     (opcional) muestra lo que haría sin escribir en BD

$cli = getopt('', [
    'config:',
    'source-siglas:',
    'source-instituto-id:',
    'dry-run',
]);

$dryRun = array_key_exists('dry-run', $cli);

$institutos = null;

if (isset($cli['config']) && is_string($cli['config']) && trim($cli['config']) !== '') {
    $path = $cli['config'];
    if (!file_exists($path)) {
        fwrite(STDERR, "❌ Config no encontrado: $path\n");
        exit(1);
    }

    $raw = file_get_contents($path);
    $json = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($json)) {
        fwrite(STDERR, "❌ Config inválido (JSON): $path\n");
        exit(1);
    }

    if (isset($json['institutos']) && is_array($json['institutos'])) {
        $institutos = $json['institutos'];
    } else {
        // Permitir que el archivo sea directamente un array
        $institutos = $json;
    }
}

if ($institutos === null) {
    $institutos = TenantSeeder::defaultInstitutos();
}

$opts = [
    'source_siglas' => isset($cli['source-siglas']) ? $cli['source-siglas'] : 'IUJO-BARQUISIMETO',
    'source_instituto_id' => isset($cli['source-instituto-id']) && is_numeric($cli['source-instituto-id'])
        ? (int)$cli['source-instituto-id']
        : null,
    'dry_run' => $dryRun,
];

$seeder = new TenantSeeder();
$result = $seeder->run($institutos, $opts);

echo "\n== Seed institutos (tenant) ==\n";
echo "source_instituto_id: " . ($result['source_instituto_id'] !== null ? $result['source_instituto_id'] : 'null') . "\n";
echo "dry_run: " . ($result['dry_run'] ? 'true' : 'false') . "\n\n";

$ok = 0;
$fail = 0;
foreach ($result['institutos'] as $row) {
    if (!empty($row['success'])) {
        $ok++;
        $id = isset($row['instituto_id']) ? (int)$row['instituto_id'] : 0;
        echo "✅ {$row['siglas']} => instituto_id={$id} | carreras={$row['seeded_carreras']} | tipo_beca={$row['seeded_tipo_beca']}\n";
    } else {
        $fail++;
        $msg = isset($row['message']) ? $row['message'] : 'Error';
        echo "❌ {$row['siglas']} | {$row['nombre']} => $msg\n";
    }
}

echo "\nResumen: ok=$ok, fail=$fail\n";

if ($fail > 0) {
    exit(1);
}
