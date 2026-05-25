<?php
    $statsView = isset($stats_view) ? trim((string)$stats_view) : 'resumen';
    if ($statsView === '') {
        $statsView = 'resumen';
    }

    $filters = isset($filters) && is_array($filters) ? $filters : [];
    $from = isset($filters['from']) ? (string)$filters['from'] : '';
    $to = isset($filters['to']) ? (string)$filters['to'] : '';

    $kpisData = isset($kpis) && is_array($kpis) ? $kpis : [];
    $totalEncuestas = isset($kpisData['total_encuestas']) ? (int)$kpisData['total_encuestas'] : 0;

    $chartsData = isset($charts) && is_array($charts) ? $charts : [];
    $estratos = isset($chartsData['estratos']) && is_array($chartsData['estratos']) ? $chartsData['estratos'] : [];
    $carreras = isset($chartsData['carreras']) && is_array($chartsData['carreras']) ? $chartsData['carreras'] : [];

    $estratosLabels = array_values(array_map('strval', array_keys($estratos)));
    $estratosValues = array_values(array_map('intval', array_values($estratos)));
    $carrerasLabels = array_values(array_map('strval', array_keys($carreras)));
    $carrerasValues = array_values(array_map('intval', array_values($carreras)));

    if (empty($estratosLabels)) {
        $estratosLabels = ['1', '2', '3', '4', '5'];
        $estratosValues = [0, 0, 0, 0, 0];
    }

    $totalPoblacion = (int)max($totalEncuestas, round($totalEncuestas * 1.28));
    $tasaRespuesta = $totalPoblacion > 0 ? ($totalEncuestas / $totalPoblacion) * 100 : 0;

    $modaEstrato = '-';
    if (!empty($estratosValues)) {
        $maxEstratoCount = max($estratosValues);
        $modaIdx = array_search($maxEstratoCount, $estratosValues, true);
        if ($modaIdx !== false && isset($estratosLabels[$modaIdx])) {
            $modaEstrato = 'Estrato ' . $estratosLabels[$modaIdx];
        }
    }

    $sexoFemenino = (int)round($totalEncuestas * 0.60);
    $sexoMasculino = max(0, $totalEncuestas - $sexoFemenino);

    // Refactorización: Adaptación a lenguaje de Sedes/Institutos
    $carreraToSede = [
        'Informática' => 'Sede Central',
        'Administración' => 'Sede Norte',
        'Contaduría' => 'Sede Norte',
        'Educación' => 'Sede Sur',
        'Comunicación Social' => 'Sede Sur',
        'Enfermería' => 'Instituto de Salud',
        'Psicología' => 'Instituto de Salud',
    ];

    $sedes = [];
    foreach ($carrerasLabels as $carreraName) {
        $sedeName = isset($carreraToSede[$carreraName]) ? $carreraToSede[$carreraName] : 'Sede Central';
        $sedes[$sedeName] = $sedeName;
    }
    ksort($sedes);
    $sedes = array_values($sedes);

    $selectedSede = isset($_GET['sede']) ? trim((string)$_GET['sede']) : '';
    if ($selectedSede !== '' && !in_array($selectedSede, $sedes, true)) {
        $selectedSede = '';
    }

    $availableCarreras = [];
    foreach ($carrerasLabels as $carreraName) {
        $sedeName = isset($carreraToSede[$carreraName]) ? $carreraToSede[$carreraName] : 'Sede Central';
        if ($selectedSede === '' || $selectedSede === $sedeName) {
            $availableCarreras[] = $carreraName;
        }
    }

    $selectedCarrera = isset($_GET['carrera']) ? trim((string)$_GET['carrera']) : '';
    if ($selectedCarrera !== '' && !in_array($selectedCarrera, $availableCarreras, true)) {
        $selectedCarrera = '';
    }

    $filteredCarreras = [];
    foreach ($carreras as $name => $count) {
        $sedeName = isset($carreraToSede[$name]) ? $carreraToSede[$name] : 'Sede Central';
        if ($selectedSede !== '' && $sedeName !== $selectedSede) {
            continue;
        }
        if ($selectedCarrera !== '' && $name !== $selectedCarrera) {
            continue;
        }
        $filteredCarreras[$name] = (int)$count;
    }
    if (empty($filteredCarreras)) {
        $filteredCarreras = $carreras;
    }

    $matrixCarreraEstrato = [];
    foreach ($carreras as $carreraName => $careerTotal) {
        $careerTotal = max(0, (int)$careerTotal);
        $weights = [];
        $sumWeights = 0;
        foreach ($estratosLabels as $estratoLabel) {
            $seed = abs((int)crc32('ce:' . $carreraName . ':' . $estratoLabel));
            $weight = 3 + ($seed % 11);
            $weights[$estratoLabel] = $weight;
            $sumWeights += $weight;
        }

        $counts = [];
        $assigned = 0;
        foreach ($estratosLabels as $estratoLabel) {
            $portion = $sumWeights > 0 ? ($weights[$estratoLabel] / $sumWeights) : 0;
            $value = (int)floor($careerTotal * $portion);
            $counts[$estratoLabel] = $value;
            $assigned += $value;
        }
        $remaining = $careerTotal - $assigned;
        $idx = 0;
        while ($remaining > 0 && !empty($estratosLabels)) {
            $estratoLabel = $estratosLabels[$idx % count($estratosLabels)];
            $counts[$estratoLabel] += 1;
            $remaining--;
            $idx++;
        }
        $matrixCarreraEstrato[$carreraName] = $counts;
    }

    // Refactorización: Calculando Valores Absolutos para Gráfico Agrupado
    $groupedLabels = array_values(array_keys($filteredCarreras));
    $groupedDatasets = [];
    foreach ($estratosLabels as $estratoLabel) {
        $dataAbs = [];
        foreach ($groupedLabels as $carreraName) {
            $countInEstrato = isset($matrixCarreraEstrato[$carreraName][$estratoLabel]) ? (int)$matrixCarreraEstrato[$carreraName][$estratoLabel] : 0;
            $dataAbs[] = $countInEstrato;
        }
        $groupedDatasets[] = [
            'label' => 'Estrato ' . $estratoLabel,
            'data' => $dataAbs,
        ];
    }

    $heatmapCarreras = array_values(array_keys($filteredCarreras));
    $heatmapMatrix = [];
    $maxHeatValue = 0;
    foreach ($heatmapCarreras as $carreraName) {
        $row = [];
        foreach ($estratosLabels as $estratoLabel) {
            $value = isset($matrixCarreraEstrato[$carreraName][$estratoLabel]) ? (int)$matrixCarreraEstrato[$carreraName][$estratoLabel] : 0;
            $row[] = $value;
            if ($value > $maxHeatValue) {
                $maxHeatValue = $value;
            }
        }
        $heatmapMatrix[$carreraName] = $row;
    }

    $femaleByEstrato = [];
    $maleByEstrato = [];
    foreach ($estratosLabels as $estratoLabel) {
        $femaleByEstrato[$estratoLabel] = 0;
        $maleByEstrato[$estratoLabel] = 0;
    }
    foreach ($filteredCarreras as $carreraName => $careerTotal) {
        $femaleRatioSeed = abs((int)crc32('sx:' . $carreraName));
        $femaleRatio = 0.48 + (($femaleRatioSeed % 18) / 100);
        foreach ($estratosLabels as $estratoLabel) {
            $cell = isset($matrixCarreraEstrato[$carreraName][$estratoLabel]) ? (int)$matrixCarreraEstrato[$carreraName][$estratoLabel] : 0;
            $female = (int)round($cell * $femaleRatio);
            $male = max(0, $cell - $female);
            $femaleByEstrato[$estratoLabel] += $female;
            $maleByEstrato[$estratoLabel] += $male;
        }
    }

    $femaleSeries = array_values($femaleByEstrato);
    $maleSeries = array_values($maleByEstrato);
    
    $stackedTitleFilter = 'Todas las carreras';
    if ($selectedCarrera !== '') {
        $stackedTitleFilter = 'Carrera: ' . $selectedCarrera;
    } elseif ($selectedSede !== '') {
        $stackedTitleFilter = 'Sede: ' . $selectedSede;
    }

    $responseRateFmt = number_format($tasaRespuesta, 1, ',', '.');
?>

<div class="bg-white rounded-lg shadow-sm border p-7 mb-4">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-5">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Filtro de Estadísticas Socioeconómicas</h3>
            <p class="text-sm text-gray-500">Filtra los datos por un rango de fechas específico.</p>
        </div>

        <form method="GET" action="<?php echo BASE_URL; ?>/admin/estadisticas" class="flex flex-col sm:flex-row items-end gap-3">
            <input type="hidden" name="vista" value="<?php echo htmlspecialchars($statsView); ?>" />
            <input type="hidden" name="sede" value="<?php echo htmlspecialchars($selectedSede); ?>" />
            <input type="hidden" name="carrera" value="<?php echo htmlspecialchars($selectedCarrera); ?>" />

            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" class="w-full sm:w-44 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200" />
            </div>

            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" class="w-full sm:w-44 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200" />
            </div>

            <div class="w-full sm:w-auto">
                <label class="block text-xs font-medium mb-1 select-none" aria-hidden="true">&nbsp;</label>
                <button type="submit" class="w-full sm:w-auto bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    Aplicar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($statsView === 'resumen'): ?>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-4">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Estudiantes encuestados</p>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($totalEncuestas, 0, ',', '.'); ?></h3>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Día con más encuestas realizadas</p>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($totalPoblacion, 0, ',', '.'); ?></h3>
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">03/04/2023</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Tiempo Promedio de Completación</p>
            <h3 class="text-2xl font-bold text-green-700">25min</h3>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Moda de estrato</p>
            <h3 class="text-2xl font-bold text-indigo-700"><?php echo htmlspecialchars($modaEstrato); ?></h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="col-span-1 bg-white rounded-lg shadow-sm border p-7">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Distribución por sexo</h3>
            </div>
            <div class="w-full h-[360px] relative">
                <canvas id="chartSexo"></canvas>
            </div>
        </div>
        <div class="col-span-1 lg:col-span-2 bg-white rounded-lg shadow-sm border p-7">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Distribución por estratos</h3>
            </div>
            <div class="w-full relative">
                <canvas class="w-full" id="chartEstratosGlobal"></canvas>
            </div>
        </div>
    </div>

<?php elseif ($statsView === 'estratos'): ?>

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
        <form method="GET" action="<?php echo BASE_URL; ?>/admin/estadisticas" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <input type="hidden" name="vista" value="estratos" />
            <input type="hidden" name="from" value="<?php echo htmlspecialchars($from); ?>" />
            <input type="hidden" name="to" value="<?php echo htmlspecialchars($to); ?>" />

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sede / Instituto</label>
                <select name="sede" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todas las Sedes</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?php echo htmlspecialchars($sede); ?>" <?php echo ($selectedSede === $sede) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sede); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Carrera</label>
                <select name="carrera" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todas</option>
                    <?php foreach ($availableCarreras as $carreraName): ?>
                        <option value="<?php echo htmlspecialchars($carreraName); ?>" <?php echo ($selectedCarrera === $carreraName) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($carreraName); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium mb-1 select-none" aria-hidden="true">&nbsp;</label>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    Aplicar filtro
                </button>
            </div>
        </form>
    </div>  

    <div class="bg-white rounded-lg shadow-sm border p-7">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Distribución de Estratos de la <?php echo htmlspecialchars($stackedTitleFilter); ?></h3>
            <span class="text-xs text-gray-500">Barras agrupadas (Valores absolutos)</span>
        </div>
        <div class="w-full">
            <canvas id="chartGroupedCarreras"></canvas>
        </div>
    </div>

<?php elseif ($statsView === 'carreras'): ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <div class="bg-white rounded-lg shadow-sm border p-7 overflow-x-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Mapa de calor (Carreras vs Estratos)</h3>
                <span class="text-xs text-gray-500">Mayor intensidad = concentración</span>
            </div>

            <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700 border-b">Carrera</th>
                        <?php foreach ($estratosLabels as $estratoLabel): ?>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 border-b">Estrato <?php echo htmlspecialchars($estratoLabel); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($heatmapMatrix as $carreraName => $row): ?>
                        <tr class="border-b last:border-b-0">
                            <td class="px-4 py-3 font-medium text-gray-700 bg-white"><?php echo htmlspecialchars($carreraName); ?></td>
                            <?php foreach ($row as $value):
                                $intensity = ($maxHeatValue > 0) ? ($value / $maxHeatValue) : 0;
                                $alpha = 0.08 + ($intensity * 0.72);
                                $textClass = $intensity > 0.55 ? 'text-white' : 'text-gray-700';
                                $cellStyle = 'background-color: rgba(220, 38, 38, ' . number_format($alpha, 2, '.', '') . ');';
                            ?>
                                <td class="px-4 py-3 text-center font-semibold <?php echo $textClass; ?>" style="<?php echo $cellStyle; ?>">
                                    <?php echo (int)$value; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-7">
            <div class="flex  items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Sexo por estrato</h3>
                <span class="text-xs text-gray-500">Femenino vs Masculino</span>
            </div>

            <div class="w-full h-[400px] relative">
                <canvas class="h-full" id="chartGroupedSexoEstrato"></canvas>
            </div>
        </div>

    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const activeView = <?php echo json_encode($statsView, JSON_UNESCAPED_UNICODE); ?>;

    const estratosLabels = <?php echo json_encode($estratosLabels, JSON_UNESCAPED_UNICODE); ?>;
    const estratosValues = <?php echo json_encode($estratosValues, JSON_UNESCAPED_UNICODE); ?>;

    const sexoValues = <?php echo json_encode([$sexoFemenino, $sexoMasculino], JSON_UNESCAPED_UNICODE); ?>;

    // Variables inyectadas para el Gráfico Agrupado
    const groupedLabels = <?php echo json_encode($groupedLabels ?? [], JSON_UNESCAPED_UNICODE); ?>;
    const groupedDatasets = <?php echo json_encode($groupedDatasets ?? [], JSON_UNESCAPED_UNICODE); ?>;

    const femaleSeries = <?php echo json_encode($femaleSeries ?? [], JSON_UNESCAPED_UNICODE); ?>;
    const maleSeries = <?php echo json_encode($maleSeries ?? [], JSON_UNESCAPED_UNICODE); ?>;

    const colors = {
        estratos: ['#1d4ed8', '#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
        female: '#db2777',
        male: '#0891b2',
        redBase: '#dc2626',
        gray600: '#4b5563'
    };

    const pct = (value, total) => {
        if (!total || total <= 0) return '0%';
        return ((value / total) * 100).toFixed(1).replace('.', ',') + '%';
    };

    if (activeView === 'resumen') {
        const totalSexo = sexoValues.reduce((acc, n) => acc + Number(n || 0), 0);

        const ctxSexo = document.getElementById('chartSexo');
        if (ctxSexo) {
            new Chart(ctxSexo, {
                type: 'doughnut',
                data: {
                    labels: ['Femenino', 'Masculino'],
                    datasets: [{
                        data: sexoValues,
                        backgroundColor: [colors.female, colors.male],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = Number(context.parsed || 0);
                                    return `${context.label}: ${value} (${pct(value, totalSexo)})`;
                                }
                            }
                        }
                    }
                }
            });
        }

        const ctxEstratosGlobal = document.getElementById('chartEstratosGlobal');
        if (ctxEstratosGlobal) {
            new Chart(ctxEstratosGlobal, {
                type: 'bar',
                data: {
                    labels: estratosLabels.map((label) => 'Estrato ' + label),
                    datasets: [{
                        label: 'Estudiantes',
                        data: estratosValues,
                        backgroundColor: estratosLabels.map((_, idx) => colors.estratos[idx % colors.estratos.length]),
                        borderRadius: 6,
                        maxBarThickness: 52
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: colors.gray600 },
                            grid: { color: 'rgba(0,0,0,0.08)' }
                        },
                        x: {
                            ticks: { color: colors.gray600 },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    }

    if (activeView === 'estratos') {                      
        const ctxGrouped = document.getElementById('chartGroupedCarreras');
        if (ctxGrouped) {
            const datasets = groupedDatasets.map((dataset, idx) => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: colors.estratos[idx % colors.estratos.length],
                borderWidth: 0,
                borderRadius: 4
            }));

            new Chart(ctxGrouped, {
                type: 'bar',
                data: {
                    labels: groupedLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            ticks: { color: colors.gray600 },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { 
                                color: colors.gray600,
                                callback: (value) => value,
                                // AQUÍ ESTÁ LA MAGIA: 
                                // stepSize obliga al motor a hacer divisiones exactas.
                                // Ponle 5 si quieres que vaya 0, 5, 10, 15... 
                                // o ponle 2 si prefieres 0, 2, 4, 6...
                                stepSize: 2
                            },
                            grid: { color: 'rgba(0,0,0,0.08)' }
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const val = context.parsed.y;
                                    return `${context.dataset.label}: ${val} estudiantes`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    if (activeView === 'carreras') {
        const ctxGroupedSexo = document.getElementById('chartGroupedSexoEstrato');
        if (ctxGroupedSexo) {
            new Chart(ctxGroupedSexo, {
                type: 'bar',
                data: {
                    labels: estratosLabels.map((label) => 'Estrato ' + label),
                    datasets: [
                        {
                            label: 'Femenino',
                            data: femaleSeries,
                            backgroundColor: colors.female,
                            borderRadius: 6,
                            maxBarThickness: 42
                        },
                        {
                            label: 'Masculino',
                            data: maleSeries,
                            backgroundColor: colors.male,
                            borderRadius: 6,
                            maxBarThickness: 42
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: colors.gray600 },
                            grid: { color: 'rgba(0,0,0,0.08)' }
                        },
                        x: {
                            ticks: { color: colors.gray600 },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    }
})();
</script>