<?php
$apiError = isset($api_error) && is_array($api_error) ? $api_error : null;

if (!$apiError) {
    return;
}

$status = isset($apiError['status']) ? (int)$apiError['status'] : 0;
$message = isset($apiError['message']) ? (string)$apiError['message'] : 'Error al cargar el reporte.';
?>
<div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-red-800 mb-4">
    <p class="font-semibold text-sm">No se pudo cargar la data del reporte.</p>
    <p class="text-sm mt-1"><?php echo htmlspecialchars($message); ?><?php echo $status > 0 ? ' (HTTP ' . $status . ')' : ''; ?></p>
</div>
