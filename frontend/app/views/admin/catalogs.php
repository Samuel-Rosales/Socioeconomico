<?php
$assetBase = BASE_URL . '/public/assets';
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <?php
    $resource = isset($resource) ? (string)$resource : '';
    $institutoId = isset($institutoId) && $institutoId !== null ? (int)$institutoId : null;
    $currentTenantScoped = !empty($currentTenantScoped);
    $institutos = isset($institutos) && is_array($institutos) ? $institutos : [];
    $carreraActivosMap = isset($carreraActivosMap) && is_array($carreraActivosMap) ? $carreraActivosMap : [];
    $editId = isset($editId) && $editId !== null ? (int)$editId : null;
    $editItem = isset($editItem) && is_array($editItem) ? $editItem : null;

    $flashClass = null;
    if (!empty($flash) && is_array($flash)) {
        $flashClass = (($flash['type'] ?? '') === 'success')
            ? 'bg-green-50 border-green-200 text-green-700'
            : 'bg-red-50 border-red-200 text-red-700';
    }

    $buildCatalogUrl = function ($res, $extra = []) use ($institutoId, $currentTenantScoped) {
        $qs = array_merge(['resource' => $res], $extra);
        if (!empty($institutoId)) {
            $qs['instituto_id'] = (int)$institutoId;
        }
        return BASE_URL . '/admin/catalogos?' . http_build_query(array_filter($qs, function ($v) {
            return $v !== null && $v !== '';
        }));
    };

    $extraCols = [];
    $candidates = ['siglas', 'codigo', 'numero', 'valor_estrato'];
    $resourcesWithValorEstrato = ['tipo-vivienda', 'fuente-ingreso', 'nivel-educacion'];
    if (!empty($catalogoItems) && is_array($catalogoItems)) {
        foreach ($catalogoItems as $r) {
            if (!is_array($r)) {
                continue;
            }
            foreach ($candidates as $c) {
                if (!in_array($c, $extraCols, true) && array_key_exists($c, $r)) {
                    $extraCols[] = $c;
                }
            }
        }
    }
    if (is_array($editItem)) {
        foreach ($candidates as $c) {
            if (!in_array($c, $extraCols, true) && array_key_exists($c, $editItem)) {
                $extraCols[] = $c;
            }
        }
    }

    if (in_array($resource, $resourcesWithValorEstrato, true) && !in_array('valor_estrato', $extraCols, true)) {
        $extraCols[] = 'valor_estrato';
    }

    $fieldConfigByResource = [
        'instituto' => ['siglas', 'nombre'],
        'semestre' => ['nombre', 'numero'],
        'rol' => ['nombre', 'codigo'],
    ];
    $defaultFields = ['nombre'];
    $fields = isset($fieldConfigByResource[$resource]) ? $fieldConfigByResource[$resource] : $defaultFields;
    if (in_array('valor_estrato', $extraCols, true) && !in_array('valor_estrato', $fields, true)) {
        $fields[] = 'valor_estrato';
    }

    $fieldMeta = [];
    foreach ($fields as $f) {
        $label = $f;
        if ($f === 'valor_estrato') $label = 'Valor estrato';
        if ($f === 'siglas') $label = 'Siglas';
        if ($f === 'codigo') $label = 'Código';
        if ($f === 'numero') $label = 'Número';
        if ($f === 'nombre') $label = 'Nombre';
        $type = ($f === 'numero' || $f === 'valor_estrato') ? 'number' : 'text';
        $fieldMeta[] = ['name' => $f, 'label' => $label, 'type' => $type];
    }

    $formatColLabel = function ($col) {
        $col = (string)$col;
        $map = [
            'valor_estrato' => 'Valor estrato',
            'siglas' => 'Siglas',
            'codigo' => 'Código',
            'numero' => 'Número',
            'nombre' => 'Nombre',
        ];

        if (isset($map[$col])) {
            return $map[$col];
        }

        $label = str_replace('_', ' ', $col);
        return ucfirst($label);
    };
    ?>

    <aside class="bg-white rounded-lg shadow-sm border border-gray-400  p-4 md:col-span-1 h-fit">
        <h3 class="font-bold text-gray-800 mb-4 px-2">Categorías</h3>
        <div class="mb-3 px-2">
            <input
                type="text"
                id="catalog-categories-search"
                placeholder="Buscar categoría..."
                class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-primary-500 focus:border-primary-500 outline-none">
        </div>
        <ul id="catalog-categories-list" class="space-y-1 text-sm">
            <?php if (!empty($catalogosMenu) && is_array($catalogosMenu)): ?>
                <?php foreach ($catalogosMenu as $item): ?>
                    <?php
                    if (!is_array($item) || empty($item['resource'])) {
                        continue;
                    }

                    $itemResource = (string)$item['resource'];
                    $itemLabel = !empty($item['label']) ? (string)$item['label'] : $itemResource;
                    $isActive = isset($resource) && (string)$resource === $itemResource;

                    $btnClass = $isActive
                        ? 'bg-primary-50 text-primary-600 font-medium'
                        : 'hover:bg-gray-50 text-gray-600';
                    ?>
                    <li class="catalog-category-item" data-label="<?php echo htmlspecialchars(mb_strtolower($itemLabel, 'UTF-8'), ENT_QUOTES); ?>">
                        <a href="<?php echo htmlspecialchars($buildCatalogUrl($itemResource)); ?>" class="block w-full text-left px-3 py-2 rounded-md <?php echo $btnClass; ?>">
                            <?php echo htmlspecialchars($itemLabel); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="text-sm text-gray-500 px-3 py-2">No hay catálogos disponibles.</li>
            <?php endif; ?>

            <li id="catalog-categories-empty" class="text-sm text-gray-500 px-3 py-2 hidden">
                No hay categorías que coincidan.
            </li>
        </ul>
    </aside>

    <div class="bg-white rounded-lg shadow-sm border border-gray-400  p-6 md:col-span-3">
        <?php if (!empty($flash) && is_array($flash)): ?>
            <div class="mb-6 p-4 rounded border <?php echo htmlspecialchars((string)$flashClass); ?> text-sm">
                <div class="font-medium"><?php echo htmlspecialchars((string)($flash['message'] ?? '')); ?></div>
                <?php if (!empty($flash['errors']) && is_array($flash['errors'])): ?>
                    <ul class="mt-2 text-sm list-disc pl-5">
                        <?php foreach ($flash['errors'] as $field => $errs): ?>
                            <?php if (is_array($errs)): ?>
                                <?php foreach ($errs as $err): ?>
                                    <li><?php echo htmlspecialchars((string)$err); ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($apiError) && is_array($apiError)): ?>
            <div class="mb-6 p-4 rounded border border-red-200 bg-red-50 text-red-700 text-sm">
                <?php echo htmlspecialchars(isset($apiError['message']) ? (string)$apiError['message'] : 'Error al cargar datos'); ?>
            </div>
        <?php endif; ?>

        <header class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars(isset($catalogoLabel) ? (string)$catalogoLabel : 'Catálogo'); ?></h3>
                <p class="text-sm text-gray-500">Gestiona las opciones disponibles para este campo.</p>
                <?php if ($currentTenantScoped): ?>
                    <?php
                    $currentInstName = '';
                    foreach ($institutos as $inst) {
                        if (is_array($inst) && isset($inst['id']) && !empty($institutoId) && (int)$inst['id'] === (int)$institutoId) {
                            $sig = isset($inst['siglas']) ? trim((string)$inst['siglas']) : '';
                            $nm = isset($inst['nombre']) ? trim((string)$inst['nombre']) : '';
                            $currentInstName = $sig !== '' ? $sig : $nm;
                            break;
                        }
                    }
                    ?>
                    <p class="text-xs text-gray-500 mt-1">
                        Sede actual: <?php echo htmlspecialchars($currentInstName !== '' ? $currentInstName : ((string)$institutoId)); ?>
                        <span class="text-gray-400">(el estado puede variar por sede)</span>
                    </p>

                    <?php if (!empty($resource) && $resource === 'carrera'): ?>
                        <p class="text-xs text-gray-500 mt-1">
                            Nota: el nombre de la carrera es global; aquí se activa/desactiva por sede.
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:items-center gap-4">

                <button type="button" id="btn-new-catalog-item" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition">
                    <i class="fas fa-plus mr-2"></i> Añadir Opción
                </button>

                <?php if ($currentTenantScoped): ?>
                    <form method="GET" action="<?php echo BASE_URL; ?>/admin/catalogos" class="flex items-center gap-2">
                        <input type="hidden" name="resource" value="<?php echo htmlspecialchars($resource); ?>">
                        <label class="text-sm text-gray-600" for="instituto_id">Sede</label>
                        <select id="instituto_id" name="instituto_id" onchange="this.form.submit()" class="border border-gray-300 rounded-md p-2 text-sm focus:ring-primary-500 focus:border-primary-500 outline-none">
                            <?php foreach ($institutos as $inst): ?>
                                <?php
                                if (!is_array($inst) || !isset($inst['id'])) {
                                    continue;
                                }
                                $iid = (int)$inst['id'];
                                $iname = isset($inst['nombre']) ? (string)$inst['nombre'] : ('Instituto #' . $iid);
                                ?>
                                <option value="<?php echo (int)$iid; ?>" <?php echo (!empty($institutoId) && (int)$institutoId === $iid) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($iname); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
            </div>
        </header>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-y">
                    <th class="py-3 px-4 font-semibold text-sm w-16">ID/Valor</th>
                    <th class="py-3 px-4 font-semibold text-sm">Nombre a mostrar</th>
                    <?php foreach ($extraCols as $c): ?>
                        <th class="py-3 px-4 font-semibold text-sm"><?php echo htmlspecialchars($formatColLabel($c)); ?></th>
                    <?php endforeach; ?>
                    <th class="py-3 px-4 font-semibold text-sm w-24 text-center">Estado</th>
                    <th class="py-3 px-4 font-semibold text-sm w-24 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y">
                <?php if (!empty($catalogoItems) && is_array($catalogoItems)): ?>
                    <?php foreach ($catalogoItems as $row): ?>
                        <?php
                        if (!is_array($row)) {
                            continue;
                        }
                        $id = isset($row['id']) ? (string)$row['id'] : '';
                        $nombre = isset($row['nombre']) ? (string)$row['nombre'] : '';
                        $activo = isset($row['activo']) ? (int)$row['activo'] : 1;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-gray-500"><?php echo htmlspecialchars($id); ?></td>
                            <td class="py-3 px-4 font-medium text-gray-800"><?php echo htmlspecialchars($nombre); ?></td>
                            <?php foreach ($extraCols as $c): ?>
                                <?php $v = array_key_exists($c, $row) ? (string)$row[$c] : ''; ?>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($v); ?></td>
                            <?php endforeach; ?>
                            <td class="py-3 px-4 text-center">
                                <?php if ($activo === 1): ?>
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Activo</span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700 mx-1 js-edit-catalog-item"
                                    title="Editar"
                                    data-id="<?php echo (int)$id; ?>"
                                    <?php if ($resource === 'carrera'): ?>
                                    <?php
                                        $aid = (int)$id;
                                        $activeList = isset($carreraActivosMap[$aid]) && is_array($carreraActivosMap[$aid]) ? $carreraActivosMap[$aid] : [];
                                        $activeJson = htmlspecialchars(json_encode(array_values(array_map('intval', $activeList))), ENT_QUOTES);
                                    ?>
                                    data-active-institutos="<?php echo $activeJson; ?>"
                                    <?php endif; ?>
                                    <?php foreach ($fields as $f): ?>
                                    <?php $dv = array_key_exists($f, $row) ? (string)$row[$f] : ''; ?>
                                    data-<?php echo htmlspecialchars($f); ?>="<?php echo htmlspecialchars($dv, ENT_QUOTES); ?>"
                                    <?php endforeach; ?>><i class="fas fa-edit"></i></button>

                                <?php if ($activo === 1): ?>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/catalogos/delete/<?php echo (int)$id; ?>" class="inline">
                                        <input type="hidden" name="resource" value="<?php echo htmlspecialchars($resource); ?>">
                                        <?php if ($currentTenantScoped && !empty($institutoId)): ?>
                                            <input type="hidden" name="instituto_id" value="<?php echo (int)$institutoId; ?>">
                                        <?php endif; ?>
                                        <button class="text-red-500 hover:text-red-700 mx-1" type="submit" title="Desactivar" onclick="return confirm('¿Desactivar este registro?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/catalogos/restore/<?php echo (int)$id; ?>" class="inline">
                                        <input type="hidden" name="resource" value="<?php echo htmlspecialchars($resource); ?>">
                                        <?php if ($currentTenantScoped && !empty($institutoId)): ?>
                                            <input type="hidden" name="instituto_id" value="<?php echo (int)$institutoId; ?>">
                                        <?php endif; ?>
                                        <button class="text-green-600 hover:text-green-800 mx-1" type="submit" title="Restaurar" onclick="return confirm('¿Restaurar este registro?');">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo (int)(4 + count($extraCols)); ?>" class="py-6 px-4 text-center text-gray-500">No hay opciones para este catálogo.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal create/edit -->
<div id="catalog-item-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/40" data-modal-close></div>
    <div class="relative mx-auto mt-16 w-full max-w-xl px-4">
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h4 id="catalog-item-modal-title" class="text-lg font-semibold text-gray-800">Nueva Opción</h4>
                <button type="button" class="text-gray-500 hover:text-gray-700" data-modal-close>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="catalog-item-form" method="POST" action="<?php echo BASE_URL; ?>/admin/catalogos/create" class="p-6">
                <input type="hidden" name="resource" id="catalog-resource" value="<?php echo htmlspecialchars($resource); ?>">
                <?php if ($currentTenantScoped && !empty($institutoId)): ?>
                    <input type="hidden" name="instituto_id" id="catalog-instituto-id" value="<?php echo (int)$institutoId; ?>">
                <?php else: ?>
                    <input type="hidden" name="instituto_id" id="catalog-instituto-id" value="">
                <?php endif; ?>
                <input type="hidden" name="prev_active_instituto_ids" id="catalog-prev-activos" value="[]">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($fieldMeta as $meta): ?>
                        <?php
                        $f = (string)$meta['name'];
                        $label = (string)$meta['label'];
                        $type = (string)$meta['type'];
                        ?>
                        <div class="<?php echo ($f === 'nombre') ? 'md:col-span-2' : ''; ?>">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo htmlspecialchars($label); ?></label>
                            <input
                                name="<?php echo htmlspecialchars($f); ?>"
                                id="catalog-<?php echo htmlspecialchars($f); ?>"
                                type="<?php echo htmlspecialchars($type); ?>"
                                class="border border-gray-300 rounded-md p-2 w-full focus:ring-primary-500 focus:border-primary-500 outline-none"
                                <?php echo ($f === 'nombre') ? 'required' : ''; ?>
                                <?php echo ($type === 'number') ? 'step="1"' : ''; ?>>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($resource === 'carrera' && !empty($institutos)): ?>
                    <div class="mt-5 pt-4 border-t">
                        <div class="text-sm font-medium text-gray-800 mb-2">Sedes donde está activa</div>
                        <p class="text-xs text-gray-500 mb-3">
                            Puedes activar o desactivar la carrera por sede (tabla Instituto_Carrera).
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <?php foreach ($institutos as $inst): ?>
                                <?php
                                if (!is_array($inst) || !isset($inst['id'])) {
                                    continue;
                                }
                                $iid = (int)$inst['id'];
                                $iname = isset($inst['nombre']) ? (string)$inst['nombre'] : ('Instituto #' . $iid);
                                $isig = isset($inst['siglas']) ? trim((string)$inst['siglas']) : '';
                                $label = $isig !== '' ? ($isig . ' — ' . $iname) : $iname;
                                $isCurrent = (!empty($institutoId) && (int)$institutoId === $iid);
                                ?>
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" class="h-4 w-4" name="instituto_activo_ids[]" value="<?php echo (int)$iid; ?>" <?php echo $isCurrent ? 'data-current="1"' : ''; ?>>
                                    <span><?php echo htmlspecialchars($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 rounded border text-gray-700 hover:bg-gray-50" data-modal-close>Cancelar</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.__CATALOGS_PAGE__ = {
        baseUrl: <?php echo json_encode(BASE_URL); ?>,
        resource: <?php echo json_encode($resource); ?>,
        institutoId: <?php echo json_encode($institutoId); ?>,
        tenantScoped: <?php echo json_encode($currentTenantScoped ? 1 : 0); ?>,
        fields: <?php echo json_encode(array_values($fields)); ?>
    };

    (function() {
        var input = document.getElementById('catalog-categories-search');
        var list = document.getElementById('catalog-categories-list');
        var empty = document.getElementById('catalog-categories-empty');

        if (!input || !list) {
            return;
        }

        var items = list.querySelectorAll('.catalog-category-item');
        if (!items.length) {
            return;
        }

        var normalize = function(value) {
            value = (value || '').toString().toLowerCase();

            if (typeof value.normalize === 'function') {
                value = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            return value;
        };

        var applyFilter = function() {
            var q = normalize(input.value);
            var visible = 0;

            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                var rawLabel = item.getAttribute('data-label') || item.textContent || '';
                var label = normalize(rawLabel);
                var show = q === '' || label.indexOf(q) !== -1;

                item.classList.toggle('hidden', !show);
                if (show) {
                    visible++;
                }
            }

            if (empty) {
                empty.classList.toggle('hidden', visible !== 0);
            }
        };

        input.addEventListener('input', applyFilter);
    })();
</script>
<script src="<?php echo $assetBase; ?>/js/admin-catalogs.js"></script>