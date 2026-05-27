<?php
    $assetBase = BASE_URL . '/assets';
    if (!isset($usuarios) || !is_array($usuarios)) {
        $usuarios = ['items' => [], 'pagination' => ['page' => 1, 'per_page' => 10, 'total' => 0, 'total_pages' => 1]];
    }
    $items = isset($usuarios['items']) && is_array($usuarios['items']) ? $usuarios['items'] : [];
    $pagination = isset($usuarios['pagination']) && is_array($usuarios['pagination']) ? $usuarios['pagination'] : ['page' => 1, 'per_page' => 10, 'total' => 0, 'total_pages' => 1];

    $q = isset($filters['q']) ? (string)$filters['q'] : '';
    $page = isset($pagination['page']) ? (int)$pagination['page'] : 1;
    $perPage = isset($pagination['per_page']) ? (int)$pagination['per_page'] : 10;
    $total = isset($pagination['total']) ? (int)$pagination['total'] : 0;
    $totalPages = isset($pagination['total_pages']) ? (int)$pagination['total_pages'] : 1;

    $actorRolCodigo = isset($actorRol) ? (string)$actorRol : null;

    $colorClasses = [
        ['bg-blue-100', 'text-blue-600'],
        ['bg-green-100', 'text-green-600'],
        ['bg-purple-100', 'text-purple-700'],
        ['bg-yellow-100', 'text-yellow-700'],
        ['bg-pink-100', 'text-pink-700'],
    ];

    $flashClass = null;
    if (!empty($flash) && is_array($flash)) {
        $flashClass = (($flash['type'] ?? '') === 'success')
            ? 'bg-green-50 border border-gray-400-green-200 text-green-700'
            : 'bg-red-50 border border-gray-400-red-200 text-red-700';
    }

    $buildQuery = function (array $overrides = []) use ($q, $perPage, $page) {
        $base = [
            'q' => $q,
            'per_page' => $perPage,
            'page' => $page,
        ];
        $params = array_merge($base, $overrides);
        return http_build_query(array_filter($params, function ($v) {
            return $v !== null && $v !== '';
        }));
    };
?>

<div class="bg-white rounded-lg shadow-sm border border-gray-400 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Lista de Usuarios</h3>
        <button type="button" id="btn-new-user" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition">
            <i class="fas fa-plus mr-2"></i> Nuevo Usuario
        </button>
    </div>

    <?php if (!empty($flash) && is_array($flash)): ?>
        <div class="mb-4 p-4 rounded border border-gray-400 <?php echo htmlspecialchars((string)$flashClass); ?>">
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
        <div class="mb-4 p-4 rounded bg-red-50 border border-gray-400-red-200 text-red-700">
            <div class="font-medium"><?php echo htmlspecialchars((string)($apiError['message'] ?? 'Error al cargar usuarios')); ?></div>
        </div>
    <?php endif; ?>

    <!-- Search/Filter -->
    <div class="mb-4 flex gap-4">
        <form id="users-filter-form" method="GET" action="<?php echo BASE_URL; ?>/admin/usuarios" class="w-full md:w-1/3">
            <input
                id="users-search"
                type="text"
                name="q"
                value="<?php echo htmlspecialchars($q); ?>"
                placeholder="Buscar por cédula, nombre o rol..."
                class=" border border-gray-400-gray-300 rounded-md p-2 w-full focus:ring-primary-500 focus:border border-gray-400-primary-500 outline-none"
            >
            <input type="hidden" name="per_page" value="<?php echo (int)$perPage; ?>">
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 text-gray-600 border-y">
                    <th class="py-3 px-4 font-semibold text-sm">Nombre</th>
                    <th class="py-3 px-4 font-semibold text-sm">Cédula</th>
                    <th class="py-3 px-4 font-semibold text-sm">Rol</th>
                    <th class="py-3 px-4 font-semibold text-sm">Instituto</th>
                    <th class="py-3 px-4 font-semibold text-sm">Fecha Registro</th>
                    <th class="py-3 px-4 font-semibold text-sm text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y">
                <?php if (empty($items)): ?>
                    <tr>
                        <td class="py-6 px-4 text-gray-500" colspan="6">No hay usuarios para mostrar.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $u): ?>
                        <?php
                            $id = isset($u['id']) ? (int)$u['id'] : 0;
                            $nombre = isset($u['nombre_completo']) ? (string)$u['nombre_completo'] : '';
                            $ci = isset($u['ci']) ? (string)$u['ci'] : '';
                            $rolNombre = isset($u['rol_nombre']) ? (string)$u['rol_nombre'] : '';
                            $rolCodigo = isset($u['rol_codigo']) ? (string)$u['rol_codigo'] : '';
                            $rolId = isset($u['rol_id']) ? (int)$u['rol_id'] : 0;
                            $institutoId = isset($u['instituto_id']) && $u['instituto_id'] !== null ? (int)$u['instituto_id'] : null;
                            $institutoLabel = isset($u['instituto_siglas']) && $u['instituto_siglas'] ? (string)$u['instituto_siglas'] : (isset($u['instituto_nombre']) ? (string)$u['instituto_nombre'] : '');
                            $creado = isset($u['creado_at']) ? (string)$u['creado_at'] : '';
                            $activo = isset($u['activo']) ? (int)$u['activo'] : 1;

                            $parts = preg_split('/\s+/', trim($nombre));
                            $initials = '';
                            if (is_array($parts) && count($parts) > 0) {
                                $initials .= mb_substr((string)$parts[0], 0, 1, 'UTF-8');
                                if (count($parts) > 1) {
                                    $initials .= mb_substr((string)$parts[count($parts) - 1], 0, 1, 'UTF-8');
                                }
                            }
                            $initials = strtoupper($initials);
                            $cIdx = $id > 0 ? ($id % count($colorClasses)) : 0;
                            $avatarBg = $colorClasses[$cIdx][0];
                            $avatarText = $colorClasses[$cIdx][1];
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 flex items-center">
                                <div class="<?php echo $avatarBg . ' ' . $avatarText; ?> rounded-full w-8 h-8 flex items-center justify-center font-bold mr-3">
                                    <?php echo htmlspecialchars($initials ?: 'U'); ?>
                                </div>
                                <?php echo htmlspecialchars($nombre); ?>
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($ci); ?></td>
                            <td class="py-3 px-4">
                                <span class="bg-primary-50 text-primary-600 px-2 py-1 rounded text-xs font-medium">
                                    <?php echo htmlspecialchars(($rolNombre !== '' ? $rolNombre : ($rolCodigo !== '' ? $rolCodigo : '—'))); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($institutoLabel ?: '—'); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($creado ?: '—'); ?></td>
                            <td class="py-3 px-4 text-right">
                                <button
                                    type="button"
                                    class="text-blue-500 hover:text-blue-700 mx-1 js-edit-user"
                                    title="Editar"
                                    data-id="<?php echo (int)$id; ?>"
                                    data-ci="<?php echo htmlspecialchars($ci, ENT_QUOTES); ?>"
                                    data-nombre="<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>"
                                    data-rol-id="<?php echo (int)$rolId; ?>"
                                    data-instituto-id="<?php echo $institutoId !== null ? (int)$institutoId : ''; ?>"
                                    data-activo="<?php echo (int)$activo; ?>"
                                ><i class="fas fa-edit"></i></button>

                                <form class="inline" method="POST" action="<?php echo BASE_URL; ?>/admin/usuarios/delete/<?php echo (int)$id; ?>" onsubmit="return confirm('¿Eliminar este usuario?');">
                                    <button type="submit" class="text-red-500 hover:text-red-700 mx-1" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex items-center justify-between border-t pt-4 text-sm text-gray-500">
        <?php
            $from = $total > 0 ? (($page - 1) * $perPage + 1) : 0;
            $to = $total > 0 ? min($page * $perPage, $total) : 0;
            $prevDisabled = $page <= 1;
            $nextDisabled = $page >= $totalPages;
        ?>
        <span>Mostrando <?php echo (int)$from; ?> a <?php echo (int)$to; ?> de <?php echo (int)$total; ?> entradas</span>
        <div class="flex gap-1">
            <a
                class="px-3 py-1 border rounded hover:bg-gray-50 <?php echo $prevDisabled ? 'pointer-events-none opacity-50' : ''; ?>"
                href="<?php echo BASE_URL; ?>/admin/usuarios?<?php echo htmlspecialchars($buildQuery(['page' => max(1, $page - 1)])); ?>"
            >Anterior</a>

            <span class="px-3 py-1 bg-primary-50 text-primary-600 border border-primary-200 rounded"><?php echo (int)$page; ?></span>

            <a
                class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 <?php echo $nextDisabled ? 'pointer-events-none opacity-50' : ''; ?>"
                href="<?php echo BASE_URL; ?>/admin/usuarios?<?php echo htmlspecialchars($buildQuery(['page' => min($totalPages, $page + 1)])); ?>"
            >Siguiente</a>
        </div>
    </div>
</div>

<!-- Modal create/edit -->
<div id="user-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/40" data-modal-close></div>
    <div class="relative mx-auto mt-16 w-full max-w-xl px-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-400">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-400">
                <h4 id="user-modal-title" class="text-lg font-semibold text-gray-800">Nuevo Usuario</h4>
                <button type="button" class="text-gray-500 hover:text-gray-700" data-modal-close>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="user-form" method="POST" action="<?php echo BASE_URL; ?>/admin/usuarios/create" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                        <input name="ci" id="user-ci" type="text" class=" border border-gray-400 rounded-md p-2 w-full focus:ring-primary-500 focus:border border-gray-400-primary-500 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select name="rol_id" id="user-rol" class=" border border-gray-400 rounded-md p-2 w-full focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                            <option value="">Seleccione...</option>
                            <?php if (!empty($roles) && is_array($roles)): ?>
                                <?php foreach ($roles as $r): ?>
                                    <?php
                                        $rid = isset($r['id']) ? (int)$r['id'] : 0;
                                        $rnombre = isset($r['nombre']) ? (string)$r['nombre'] : '';
                                        $rcodigo = isset($r['codigo']) ? (string)$r['codigo'] : '';
                                        $rlabel = $rnombre !== '' ? $rnombre : ($rcodigo !== '' ? $rcodigo : ('Rol #' . $rid));
                                    ?>
                                    <option value="<?php echo (int)$rid; ?>"><?php echo htmlspecialchars($rlabel); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                        <input name="nombre_completo" id="user-nombre" type="text" class=" border border-gray-400 rounded-md p-2 w-full focus:ring-primary-500 focus:border-primary-500 outline-none" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input name="password" id="user-password" type="password" class=" border border-gray-400 rounded-md p-2 w-full focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <p id="user-password-help" class="text-xs text-gray-500 mt-1">Requerida al crear. En edición, déjala vacía para mantener la actual.</p>
                    </div>

                    <?php if ($actorRolCodigo === 'SUPER_ADMIN'): ?>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instituto</label>
                            <select name="instituto_id" id="user-instituto" class="border border-gray-400 rounded-md p-2 w-full focus:ring-primary-500 focus:border-primary-500 outline-none">
                                <option value="">(Sin instituto: solo Administrador Global)</option>
                                <?php if (!empty($institutos) && is_array($institutos)): ?>
                                    <?php foreach ($institutos as $inst): ?>
                                        <?php
                                            $iid = isset($inst['id']) ? (int)$inst['id'] : 0;
                                            $ilabel = '';
                                            if (!empty($inst['siglas'])) {
                                                $ilabel = (string)$inst['siglas'];
                                            } elseif (!empty($inst['nombre'])) {
                                                $ilabel = (string)$inst['nombre'];
                                            }
                                        ?>
                                        <option value="<?php echo (int)$iid; ?>"><?php echo htmlspecialchars($ilabel ?: ('Instituto #' . (int)$iid)); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="md:col-span-2 flex items-center gap-2">
                        <input name="activo" id="user-activo" type="checkbox" class="h-4 w-4" checked>
                        <label for="user-activo" class="text-sm text-gray-700">Activo</label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 rounded border border-gray-400 text-gray-700 hover:bg-gray-50" data-modal-close>Cancelar</button>
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.__USERS_PAGE__ = {
        baseUrl: <?php echo json_encode(BASE_URL); ?>,
        actorRol: <?php echo json_encode($actorRolCodigo); ?>
    };
</script>
<script src="<?php echo $assetBase; ?>/js/admin-users.js"></script>
