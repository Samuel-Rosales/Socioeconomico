<?php
$f = isset($filtros) && is_array($filtros) ? $filtros : [];
$catalogo = isset($filtros_catalogo) && is_array($filtros_catalogo) ? $filtros_catalogo : [];

$from = isset($f['from']) ? (string)$f['from'] : '';
$to = isset($f['to']) ? (string)$f['to'] : '';
$institutoId = isset($f['instituto_id']) ? (int)$f['instituto_id'] : null;
$carreraId = isset($f['carrera_id']) ? (int)$f['carrera_id'] : null;

$institutos = isset($catalogo['institutos']) && is_array($catalogo['institutos']) ? $catalogo['institutos'] : [];
$carreras = isset($catalogo['carreras']) && is_array($catalogo['carreras']) ? $catalogo['carreras'] : [];
$isSuperAdmin = !empty($is_super_admin);
$action = isset($filtros_action) ? (string)$filtros_action : '';
$viewKey = isset($report_view_key) ? (string)$report_view_key : '';
?>
<div class="bg-white rounded-lg shadow-sm border p-5 mb-5">
    <form method="GET" action="<?php echo htmlspecialchars($action); ?>" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($viewKey); ?>" />

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
            <input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200" />
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
            <input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200" />
        </div>

        <?php if ($isSuperAdmin): ?>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Instituto</label>
                <select name="instituto_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                    <option value="">Todos</option>
                    <?php foreach ($institutos as $it):
                        $id = isset($it['id']) ? (int)$it['id'] : 0;
                        $name = isset($it['nombre']) ? (string)$it['nombre'] : '';
                        $siglas = isset($it['siglas']) ? (string)$it['siglas'] : '';
                        $selected = ($institutoId !== null && $institutoId === $id) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $id; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($name . ($siglas !== '' ? ' (' . $siglas . ')' : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Carrera</label>
            <select name="carrera_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-200">
                <option value="">Todas</option>
                <?php foreach ($carreras as $ca):
                    $id = isset($ca['id']) ? (int)$ca['id'] : 0;
                    $name = isset($ca['nombre']) ? (string)$ca['nombre'] : '';
                    $selected = ($carreraId !== null && $carreraId === $id) ? 'selected' : '';
                ?>
                    <option value="<?php echo $id; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                Aplicar filtros
            </button>
        </div>
    </form>
</div>
