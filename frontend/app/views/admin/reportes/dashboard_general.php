<?php
$data = isset($report_data) && is_array($report_data) ? $report_data : [];
$kpis = isset($data['kpis']) && is_array($data['kpis']) ? $data['kpis'] : [];
$sexo = isset($data['sexo']) && is_array($data['sexo']) ? $data['sexo'] : ['labels' => [], 'values' => []];
$estratos = isset($data['estratos']) && is_array($data['estratos']) ? $data['estratos'] : ['labels' => [], 'values' => []];

$totalEncuestados = isset($kpis['total_encuestados']) ? (int)$kpis['total_encuestados'] : 0;
$diaMasEncuestasTotal = isset($kpis['dia_mas_encuestas_total']) ? (int)$kpis['dia_mas_encuestas_total'] : 0;
$diaMasEncuestasFechaRaw = isset($kpis['dia_mas_encuestas_fecha']) ? (string)$kpis['dia_mas_encuestas_fecha'] : '';
$diaMasEncuestasFecha = 'Sin datos';
if ($diaMasEncuestasFechaRaw !== '') {
    $dtDiaMas = \DateTime::createFromFormat('Y-m-d', $diaMasEncuestasFechaRaw);
    if ($dtDiaMas instanceof \DateTime) {
        $diaMasEncuestasFecha = $dtDiaMas->format('d/m/Y');
    } else {
        $diaMasEncuestasFecha = $diaMasEncuestasFechaRaw;
    }
}
$tasaRespuesta = isset($kpis['tasa_respuesta']) ? (float)$kpis['tasa_respuesta'] : 0;
$tiempoPromedioRespuestaMinutos = isset($kpis['tiempo_promedio_respuesta_minutos']) ? (float)$kpis['tiempo_promedio_respuesta_minutos'] : 0;
$encuestasConTiempo = isset($kpis['encuestas_con_tiempo']) ? (int)$kpis['encuestas_con_tiempo'] : 0;
$modaEstrato = isset($kpis['moda_estrato']) ? (string)$kpis['moda_estrato'] : 'Sin dato';

$sexoLabels = isset($sexo['labels']) && is_array($sexo['labels']) ? $sexo['labels'] : [];
$sexoValues = isset($sexo['values']) && is_array($sexo['values']) ? $sexo['values'] : [];
$estratosLabels = isset($estratos['labels']) && is_array($estratos['labels']) ? $estratos['labels'] : [];
$estratosValues = isset($estratos['values']) && is_array($estratos['values']) ? $estratos['values'] : [];

require __DIR__ . '/partials/filtros.php';
require __DIR__ . '/partials/estado.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-lg shadow-sm border p-5">
        <p class="text-xs uppercase tracking-wide text-gray-500">Total encuestados</p>
        <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo number_format($totalEncuestados, 0, ',', '.'); ?></h3>
    </div>
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Día con más encuestas</p>
        <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($diaMasEncuestasTotal, 0, ',', '.'); ?></h3>
        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1"><?php echo htmlspecialchars($diaMasEncuestasFecha); ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border p-5">
        <p class="text-xs uppercase tracking-wide text-gray-500">Tiempo promedio de respuesta</p>
        <h3 class="text-2xl font-bold text-green-700 mt-1"><?php echo number_format($tiempoPromedioRespuestaMinutos, 2, ',', '.'); ?> min</h3>
        <p class="text-xs text-gray-500 mt-1">Basado en <?php echo number_format($encuestasConTiempo, 0, ',', '.'); ?> encuestas</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border p-5">
        <p class="text-xs uppercase tracking-wide text-gray-500">Moda de estrato</p>
        <h3 class="text-2xl font-bold text-indigo-700 mt-1"><?php echo "Estrato " . htmlspecialchars($modaEstrato); ?></h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg shadow-sm border p-5 lg:col-span-1">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribución por sexo</h3>
        <div class="h-80 relative">
            <canvas id="chartSexo"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border p-5 lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribución por estrato</h3>
        <div class="h-80 relative">
            <canvas id="chartEstratos"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const sexoLabels = <?php echo json_encode(array_values($sexoLabels), JSON_UNESCAPED_UNICODE); ?>;
    const sexoValues = <?php echo json_encode(array_values($sexoValues), JSON_UNESCAPED_UNICODE); ?>;
    const estratosLabels = <?php echo json_encode(array_values($estratosLabels), JSON_UNESCAPED_UNICODE); ?>;
    const estratosValues = <?php echo json_encode(array_values($estratosValues), JSON_UNESCAPED_UNICODE); ?>;

    const sexoCanvas = document.getElementById('chartSexo');
    if (sexoCanvas && sexoLabels.length > 0) {
        new Chart(sexoCanvas, {
            type: 'doughnut',
            data: {
                labels: sexoLabels,
                datasets: [{
                    data: sexoValues,
                    backgroundColor: ['#0ea5e9', '#ec4899', '#64748b', '#22c55e'],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    const estratosCanvas = document.getElementById('chartEstratos');
    if (estratosCanvas && estratosLabels.length > 0) {
        new Chart(estratosCanvas, {
            type: 'bar',
            data: {
                labels: estratosLabels,
                datasets: [{
                    label: 'Encuestados',
                    data: estratosValues,
                    backgroundColor: '#1d4ed8',
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }
})();
</script>
