<?php
$data = isset($report_data) && is_array($report_data) ? $report_data : [];
$labels = isset($data['labels']) && is_array($data['labels']) ? $data['labels'] : [];
$datasets = isset($data['datasets']) && is_array($data['datasets']) ? $data['datasets'] : [];

require __DIR__ . '/partials/filtros.php';
require __DIR__ . '/partials/estado.php';
?>

<div class="bg-white rounded-lg shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Composición por estratos en carreras</h3>
        <span class="text-xs text-gray-500">Barras por porcentaje</span>
    </div>
    <div class="w-full ">
        <canvas id="chartAnalisisAcademico"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = <?php echo json_encode(array_values($labels), JSON_UNESCAPED_UNICODE); ?>;
    const rawDatasets = <?php echo json_encode(array_values($datasets), JSON_UNESCAPED_UNICODE); ?>;

    const palette = ['#1d4ed8', '#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#64748b'];
    const datasets = rawDatasets.map((item, index) => ({
        label: item.label || ('Serie ' + (index + 1)),
        data: Array.isArray(item.values.series) ? item.values.series : [],
        totals: Array.isArray(item.values.totals) ? item.values.totals : [],
        backgroundColor: palette[index % palette.length],
        borderWidth: 0,
        borderRadius: 4
    }));

    const canvas = document.getElementById('chartAnalisisAcademico');
    if (!canvas || labels.length === 0) {
        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                x: { 
                    ticks: { color: '#4b5563'},
                    grid: { display: false },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#4b5563',
                        callback: (value) => value,
                        stepSize: 10
                    },
                    grid: { color: 'rgba(0, 0, 0, 0.08)' }
                },
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const percentage = Number(ctx.parsed.y || 0);
                            const totals = Array.isArray(ctx.dataset.totals) ? ctx.dataset.totals : [];
                            const total = Number(totals[ctx.dataIndex] || 0);
                            return `${ctx.dataset.label}: ${total} (${percentage.toFixed(2)}%)`;
                        },
                    },
                },
            }
        },
    });
})();
</script>
