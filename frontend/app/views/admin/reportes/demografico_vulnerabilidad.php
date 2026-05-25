<?php
$data = isset($report_data) && is_array($report_data) ? $report_data : [];

$heatmap = isset($data['heatmap']) && is_array($data['heatmap']) ? $data['heatmap'] : [];
$rows = isset($heatmap['rows']) && is_array($heatmap['rows']) ? $heatmap['rows'] : [];
$columns = isset($heatmap['columns']) && is_array($heatmap['columns']) ? $heatmap['columns'] : [];
$values = isset($heatmap['values']) && is_array($heatmap['values']) ? $heatmap['values'] : [];

$sexoEstrato = isset($data['sexo_por_estrato']) && is_array($data['sexo_por_estrato']) ? $data['sexo_por_estrato'] : [];
$sexoLabels = isset($sexoEstrato['labels']) && is_array($sexoEstrato['labels']) ? $sexoEstrato['labels'] : [];
$femenino = isset($sexoEstrato['femenino']) && is_array($sexoEstrato['femenino']) ? $sexoEstrato['femenino'] : [];
$masculino = isset($sexoEstrato['masculino']) && is_array($sexoEstrato['masculino']) ? $sexoEstrato['masculino'] : [];

$maxHeatValue = 0;
foreach ($values as $r) {
    if (!is_array($r)) {
        continue;
    }
    foreach ($r as $v) {
        $iv = (int)$v;
        if ($iv > $maxHeatValue) {
            $maxHeatValue = $iv;
        }
    }
}

require __DIR__ . '/partials/filtros.php';
require __DIR__ . '/partials/estado.php';
?>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
    <div class="bg-white rounded-lg shadow-sm border p-5 overflow-x-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Cuadro Comparativo de Carreras por Estratos</h3>
            <span class="text-xs text-gray-500">Valores absolutos</span>
        </div>

        <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left border-b">Carrera</th>
                    <?php foreach ($columns as $col): ?>
                        <th class="px-4 py-3 text-center border-b"><?php echo htmlspecialchars((string)$col); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $rowIdx => $name):
                    $line = isset($values[$rowIdx]) && is_array($values[$rowIdx]) ? $values[$rowIdx] : [];
                ?>
                    <tr class="border-b last:border-b-0">
                        <td class="px-4 py-3 font-medium text-gray-700 bg-white"><?php echo htmlspecialchars((string)$name); ?></td>
                        <?php foreach ($line as $val):
                            $v = (int)$val;
                            $intensity = $maxHeatValue > 0 ? ($v / $maxHeatValue) : 0;
                            $alpha = 0.08 + ($intensity * 0.72);
                            $textClass = $intensity > 0.55 ? 'text-white' : 'text-gray-700';
                        ?>
                            <td class="px-4 py-3 text-center font-semibold <?php echo $textClass; ?>" style="background-color: rgba(220, 38, 38, <?php echo number_format($alpha, 2, '.', ''); ?>)">
                                <?php echo $v; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-5 h-full flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Sexo por estrato</h3>
            <span class="text-xs text-gray-500">Barras agrupadas</span>
        </div>
        <div class="w-full flex-1 min-h-[260px] relative">
            <canvas id="chartSexoEstrato" class="w-full h-full"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = <?php echo json_encode(array_values($sexoLabels), JSON_UNESCAPED_UNICODE); ?>;
    const femenino = <?php echo json_encode(array_values($femenino), JSON_UNESCAPED_UNICODE); ?>;
    const masculino = <?php echo json_encode(array_values($masculino), JSON_UNESCAPED_UNICODE); ?>;

    const canvas = document.getElementById('chartSexoEstrato');
    if (!canvas || labels.length === 0) {
        return;
    }
    const labelsWithStratos = labels.map((label) => 'Estrato ' + label);

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labelsWithStratos,
            datasets: [
                {
                    label: 'Femenino',
                    data: femenino,
                    backgroundColor: '#db2777',
                    borderRadius: 4,
                },
                {
                    label: 'Masculino',
                    data: masculino,
                    backgroundColor: '#0891b2',
                    borderRadius: 4,
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
        },
    });
})();
</script>
