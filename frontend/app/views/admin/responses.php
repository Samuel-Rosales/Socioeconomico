<!-- Responses management -->
<div class="bg-white rounded-lg shadow-sm border border-gray-400  p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Respuestas Recibidas</h3>
        <div class="flex gap-2">
            <button id="btnExportarExcel" type="button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition">
                <i class="fas fa-file-excel mr-2"></i> Exportar Excel
            </button>
            <a href="<?php echo BASE_URL; ?>/admin/encuestas/nueva" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition">
                <i class="fas fa-plus mr-2"></i> Nueva Encuesta
            </a>
        </div>
    </div>

    <?php
        $filters = isset($filters) && is_array($filters) ? $filters : [];
        $carreras = isset($carreras) && is_array($carreras) ? $carreras : [];
        $encuestas = isset($encuestas) && is_array($encuestas) ? $encuestas : ['items' => [], 'pagination' => []];

        $items = isset($encuestas['items']) && is_array($encuestas['items']) ? $encuestas['items'] : [];
        $pagination = isset($encuestas['pagination']) && is_array($encuestas['pagination']) ? $encuestas['pagination'] : [];

        $page = isset($pagination['page']) ? (int)$pagination['page'] : (isset($filters['page']) ? (int)$filters['page'] : 1);
        $perPage = isset($pagination['per_page']) ? (int)$pagination['per_page'] : (isset($filters['per_page']) ? (int)$filters['per_page'] : 10);
        $total = isset($pagination['total']) ? (int)$pagination['total'] : count($items);
        $totalPages = isset($pagination['total_pages']) ? (int)$pagination['total_pages'] : 1;

        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 10;
        if ($totalPages < 1) $totalPages = 1;

        $countOnPage = count($items);
        $from = $countOnPage > 0 ? (($page - 1) * $perPage + 1) : 0;
        $to = $countOnPage > 0 ? (($page - 1) * $perPage + $countOnPage) : 0;

        $baseParams = [];
        if (!empty($filters['q'])) $baseParams['q'] = (string)$filters['q'];
        if (!empty($filters['carrera_id'])) $baseParams['carrera_id'] = (string)$filters['carrera_id'];
        if (!empty($filters['estrato'])) $baseParams['estrato'] = (string)$filters['estrato'];
        if (!empty($filters['instituto_id'])) $baseParams['instituto_id'] = (string)$filters['instituto_id'];
        if (!empty($filters['per_page'])) $baseParams['per_page'] = (int)$filters['per_page'];

        $buildUrl = function (array $overrides = []) use ($baseParams) {
            $params = array_merge($baseParams, $overrides);
            $qs = http_build_query($params);
            return BASE_URL . '/admin/respuestas' . ($qs ? ('?' . $qs) : '');
        };

        $estratoBadgeClasses = [
            1 => 'bg-primary2-50 text-primary2-900 border border-primary2-200',
            2 => 'bg-primary2-100 text-primary2-800 border border-primary2-300',
            3 => 'bg-primary2-200 text-primary2-800 border border-primary2-400',
            4 => 'bg-primary2-300 text-primary2-900 border border-primary2-500',
            5 => 'bg-primary2-500 text-white border border-primary2-600',
        ];
    ?>

    <?php if (isset($apiError) && is_array($apiError) && !empty($apiError['message'])): ?>
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?php echo htmlspecialchars((string)$apiError['message']); ?>
            <?php if (!empty($apiError['status']) && (int)$apiError['status'] === 401): ?>
                <span class="ml-2">Vuelve a iniciar sesión.</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Search/Filter -->
    <?php $gridCols = (!empty($is_super_admin) && isset($institutos) && is_array($institutos)) ? 'md:grid-cols-5' : 'md:grid-cols-4'; ?>
    <form method="GET" action="<?php echo BASE_URL; ?>/admin/respuestas" class="mb-4 grid grid-cols-1 <?php echo $gridCols; ?> gap-4">
        <input
            type="text"
            name="q"
            value="<?php echo isset($filters['q']) ? htmlspecialchars((string)$filters['q']) : ''; ?>"
            placeholder="Buscar por nombre o cédula..."
            class="border border-gray-300 rounded-md p-2 focus:ring-primary-500 focus:border-primary-500 outline-none md:col-span-2"
        >

        <input type="hidden" name="per_page" value="<?php echo isset($filters['per_page']) ? (int)$filters['per_page'] : 10; ?>">

        <?php if (!empty($is_super_admin) && isset($institutos) && is_array($institutos)): ?>
            <select
                name="instituto_id"
                class="border border-gray-300 rounded-md p-2 focus:ring-primary-500 focus:border-primary-500 outline-none"
                onchange="this.form.submit()"
            >
                <option value="">Todos los institutos</option>
                <?php foreach ($institutos as $instituto): ?>
                    <?php
                        $id = isset($instituto['id']) ? (int)$instituto['id'] : 0;
                        $nombre = isset($instituto['nombre']) ? (string)$instituto['nombre'] : '';
                        $siglas = isset($instituto['siglas']) ? (string)$instituto['siglas'] : '';
                        $selected = (isset($filters['instituto_id']) && (string)$filters['instituto_id'] !== '' && (int)$filters['instituto_id'] === $id);
                    ?>
                    <option value="<?php echo $id; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($nombre . ($siglas !== '' ? ' (' . $siglas . ')' : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input type="hidden" name="instituto_id" value="">
        <?php endif; ?>

        <select
            name="carrera_id"
            class="border border-gray-300 rounded-md p-2 focus:ring-primary-500 focus:border-primary-500 outline-none"
            onchange="this.form.submit()"
        >
            <option value="">Todas las carreras</option>
            <?php foreach ($carreras as $carrera): ?>
                <?php
                    $id = isset($carrera['id']) ? (int)$carrera['id'] : 0;
                    $nombre = isset($carrera['nombre']) ? (string)$carrera['nombre'] : '';
                    $selected = (isset($filters['carrera_id']) && (string)$filters['carrera_id'] !== '' && (int)$filters['carrera_id'] === $id);
                ?>
                <option value="<?php echo $id; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($nombre); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select
            name="estrato"
            class="border border-gray-300 rounded-md p-2 focus:ring-primary-500 focus:border-primary-500 outline-none"
            onchange="this.form.submit()"
        >
            <option value="">Estrato</option>
            <option value="pendiente" <?php echo (isset($filters['estrato']) && ($filters['estrato'] === 'pendiente' || $filters['estrato'] === 'incompleta')) ? 'selected' : ''; ?>>Pendiente</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo (isset($filters['estrato']) && (string)$filters['estrato'] === (string)$i) ? 'selected' : ''; ?>>Estrato <?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-y">
                    <th class="py-3 px-4 font-semibold text-sm">Estudiante</th>
                    <th class="py-3 px-4 font-semibold text-sm">Cédula</th>
                    <th class="py-3 px-4 font-semibold text-sm">Carrera</th>
                    <th class="py-3 px-4 font-semibold text-sm">Fecha</th>
                    <th class="py-3 px-4 font-semibold text-sm">Estrato</th>
                    <th class="py-3 px-4 font-semibold text-sm text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y">
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $encuesta): ?>
                        <?php
                            $id = isset($encuesta['id']) ? (int)$encuesta['id'] : 0;
                            $estudiante = isset($encuesta['estudiante']) ? (string)$encuesta['estudiante'] : '';
                            $cedula = isset($encuesta['cedula']) ? (string)$encuesta['cedula'] : '';
                            $carrera = isset($encuesta['carrera']) ? (string)$encuesta['carrera'] : '';
                            $creado = isset($encuesta['creado']) ? (string)$encuesta['creado'] : '';
                            $estrato = array_key_exists('estrato', $encuesta) ? $encuesta['estrato'] : null;
                            $hasEstrato = $estrato !== null && $estrato !== '';
                            $estratoNum = is_numeric($estrato) ? (int)$estrato : null;
                            $estratoBadgeClass = ($estratoNum !== null && isset($estratoBadgeClasses[$estratoNum]))
                                ? $estratoBadgeClasses[$estratoNum]
                                : 'bg-gray-100 text-gray-700 border border-gray-300';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($estudiante); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($cedula); ?></td>
                            <td class="py-3 px-4 text-gray-500"><?php echo htmlspecialchars($carrera); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($creado); ?></td>
                            <td class="py-3 px-4">
                                <?php if ($hasEstrato): ?>
                                    <span class="px-2 py-1 rounded text-xs font-medium inline-block <?php echo htmlspecialchars($estratoBadgeClass); ?>"><?php echo htmlspecialchars((string)$estrato); ?></span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium border border-gray-200">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a class="text-indigo-500 hover:text-indigo-700 mx-1" href="<?php echo BASE_URL; ?>/admin/respuestas/<?php echo $id; ?>" title="Ver Detalles"><i class="fas fa-eye"></i></a>
                                <!--    <button class="text-blue-500 hover:text-blue-700 mx-1" title="Editar"><i class="fas fa-edit"></i></button> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td class="py-6 px-4 text-center text-gray-500" colspan="7">No hay resultados para los filtros actuales.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex items-center justify-between border-t pt-4 text-sm text-gray-500">
        <span>Mostrando <?php echo $from; ?> a <?php echo $to; ?> de <?php echo $total; ?> entradas</span>
        <div class="flex gap-1">
            <?php if ($page <= 1): ?>
                <span class="px-3 py-1 border rounded opacity-50 cursor-not-allowed">Anterior</span>
            <?php else: ?>
                <a class="px-3 py-1 border rounded hover:bg-gray-50" href="<?php echo htmlspecialchars($buildUrl(['page' => $page - 1])); ?>">Anterior</a>
            <?php endif; ?>

            <?php
                $pageSet = [$page - 1, $page, $page + 1, 1, $totalPages];
                $pageSet = array_filter($pageSet, function ($p) use ($totalPages) {
                    return is_int($p) && $p >= 1 && $p <= $totalPages;
                });
                $pageSet = array_values(array_unique($pageSet));
                sort($pageSet);
                $last = null;
            ?>

            <?php foreach ($pageSet as $p): ?>
                <?php if ($last !== null && $p > $last + 1): ?>
                    <span class="px-2 py-1">...</span>
                <?php endif; ?>

                <?php if ($p === $page): ?>
                    <span class="px-3 py-1 bg-primary-50 text-primary-600 border border-primary-200 rounded"><?php echo $p; ?></span>
                <?php else: ?>
                    <a class="px-3 py-1 border rounded hover:bg-gray-50" href="<?php echo htmlspecialchars($buildUrl(['page' => $p])); ?>"><?php echo $p; ?></a>
                <?php endif; ?>

                <?php $last = $p; ?>
            <?php endforeach; ?>

            <?php if ($page >= $totalPages): ?>
                <span class="px-3 py-1 border rounded opacity-50 cursor-not-allowed">Siguiente</span>
            <?php else: ?>
                <a class="px-3 py-1 border rounded hover:bg-gray-50" href="<?php echo htmlspecialchars($buildUrl(['page' => $page + 1])); ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.getElementById('btnExportarExcel').addEventListener('click', async () => {
        const params = new URLSearchParams(window.location.search);

        const authToken = <?php echo json_encode($_SESSION['auth_token'] ?? ''); ?>;
        if (!authToken) {
            alert('Sesión expirada. Por favor recarga la página.');
            return;
        }

        try {
            const response = await fetch('<?php echo rtrim(API_BASE_URL, '/'); ?>/exportar/encuestas-excel?' + params.toString(), {
                headers: {
                    'Authorization': 'Bearer ' + authToken
                }
            });

            if (!response.ok) {
                throw new Error('Error del servidor: ' + response.status);
            }

            const contentType = response.headers.get('Content-Type');
            if (!contentType || !contentType.includes('spreadsheetml')) {
                const text = await response.text();
                throw new Error('Respuesta inválida del servidor');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'encuestas_<?php echo date('Y-m-d_His'); ?>.xlsx';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            alert('Error al exportar: ' + error.message);
        }
    });
    </script>
</div>
