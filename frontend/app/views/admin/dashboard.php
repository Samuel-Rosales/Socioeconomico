<?php
    $dashboardData = isset($dashboard) && is_array($dashboard) ? $dashboard : [];
    $totalEncuestas = isset($dashboardData['total_encuestas']) && $dashboardData['total_encuestas'] !== null ? (int)$dashboardData['total_encuestas'] : null;
    $totalUsuarios = isset($dashboardData['total_usuarios']) && $dashboardData['total_usuarios'] !== null ? (int)$dashboardData['total_usuarios'] : null;
    $ultimaEncuesta = isset($dashboardData['ultima_encuesta']) && $dashboardData['ultima_encuesta'] !== null ? (string)$dashboardData['ultima_encuesta'] : null;

    $ultimaEncuestaBonita = null;
    if ($ultimaEncuesta !== null && trim($ultimaEncuesta) !== '') {
        $ultimaEncuestaBonita = formatFechaUTC($ultimaEncuesta);
    }

    $recientes = isset($encuestasRecientes) && is_array($encuestasRecientes) ? $encuestasRecientes : [];

    $estratoBadgeClasses = [
        1 => 'bg-primary2-50 text-primary2-900 border border-gray-400 border border-gray-400-primary2-200',
        2 => 'bg-primary2-100 text-primary2-800 border border-gray-400 border border-gray-400-primary2-300',
        3 => 'bg-primary2-200 text-primary2-800 border border-gray-400 border border-gray-400-primary2-400',
        4 => 'bg-primary2-300 text-primary2-900 border border-gray-400 border border-gray-400-primary2-500',
        5 => 'bg-primary2-500 text-white border border-gray-400 border border-gray-400-primary2-600',
    ];
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-white rounded-lg shadow-sm border border-gray-400 p-5">
        <div class="flex items-center">
            <div class="p-3 rounded-full text-primary2-400 mr-4">
                <i class="fas fa-file-invoice text-3xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide  text-gray-1000">Total Encuestas</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $totalEncuestas !== null ? number_format($totalEncuestas, 0, ',', '.') : '—'; ?></h3>
            </div>
        </div>
    </div>
    <?php if (isset($totalUsuarios)): ?>  
        <div class="bg-white rounded-lg shadow-sm border border-gray-400 p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full text-primary2-400 mr-4">
                    <i class="fas fa-users text-3xl"></i>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-1000">Usuarios</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $totalUsuarios !== null ? number_format($totalUsuarios, 0, ',', '.') : '—'; ?></h3>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-400 p-5">
        <div class="flex items-center">
            <div class="p-3 rounded-full text-primary2-400 mr-4">
                <i class="fas fa-calendar-alt text-3xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-1000">Última encuesta</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1"><?php echo $ultimaEncuestaBonita !== null ? htmlspecialchars($ultimaEncuestaBonita) : '—'; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="bg-white rounded-lg shadow-sm border border-gray-400 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Últimas Encuestas</h3>
            <a href="<?php echo BASE_URL; ?>/admin/respuestas" class="text-sm text-blue-500 hover:underline">Ver todas</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm">
                        <th class="py-2 px-4 border-b">Estudiante</th>
                        <th class="py-2 px-4 border-b">Cédula</th>
                        <th class="py-2 px-4 border-b">Fecha</th>
                        <th class="py-2 px-4 border-b">Estrato</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($recientes)): ?>
                        <tr>
                            <td class="py-6 px-4 text-gray-1000" colspan="4">No hay encuestas para mostrar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recientes as $row): ?>
                            <?php
                                $estudiante = isset($row['estudiante']) ? (string)$row['estudiante'] : '';
                                $cedula = isset($row['cedula']) ? (string)$row['cedula'] : '';
                                $fecha = isset($row['creado']) ? (string)$row['creado'] : '';
                                $estrato = isset($row['estrato']) ? $row['estrato'] : null;
                                $estratoNum = is_numeric($estrato) ? (int)$estrato : null;
                                $estratoBadgeClass = ($estratoNum !== null && isset($estratoBadgeClasses[$estratoNum]))
                                    ? $estratoBadgeClasses[$estratoNum]
                                    : 'bg-gray-100 text-gray-700 border border-gray-300';
                            ?>
                            <tr class="border-b hover:bg-gray-100 transition-colors">
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($estudiante ?: '—'); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($cedula ?: '—'); ?></td>
                                <td class="py-3 px-4 border-b"><?php echo htmlspecialchars($fecha ?: '—'); ?></td>
                                <td class="py-3 px-4 border-b">
                                    <?php if ($estrato): ?>
                                    <span class="px-2 py-1 rounded text-xs font-medium inline-block <?php echo htmlspecialchars($estratoBadgeClass); ?>"><?php echo htmlspecialchars((string)$estrato); ?></span>
                                <?php else: ?>
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-medium border border-gray-200">Sin estrato</span>
                                <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-400 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Accesos Rápidos</h3>
        <ul class="space-y-3">
            <?php
                $dashRol = isset($_SESSION['auth_user']['rol']['codigo']) ? $_SESSION['auth_user']['rol']['codigo'] : null;
                $dashIsSuperAdmin = ($dashRol === 'SUPER_ADMIN');
            ?>

            <li>
                <a href="<?php echo BASE_URL; ?>/admin/reportes/dashboard-general" class="flex items-center p-3 hover:bg-gray-100 rounded border border-gray-400 transition">
                    <div class="p-2 bg-indigo-100 text-indigo-600 rounded mr-3"><i class="fas fa-chart-pie"></i></div>
                    <div>
                        <p class="font-medium text-gray-800">Dashboard general</p>
                        <p class="text-xs text-gray-1000">Resumen ejecutivo con KPIs y distribución general.</p>
                    </div>
                </a>
            </li>

            <li>
                <a href="<?php echo BASE_URL; ?>/admin/reportes/analisis-academico" class="flex items-center p-3 hover:bg-gray-100 rounded border border-gray-400 transition">
                    <div class="p-2 bg-cyan-100 text-cyan-600 rounded mr-3"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <p class="font-medium text-gray-800">Análisis académico</p>
                        <p class="text-xs text-gray-1000">Composición porcentual por carrera y estrato.</p>
                    </div>
                </a>
            </li>

            <li>
                <a href="<?php echo BASE_URL; ?>/admin/reportes/demografico-vulnerabilidad" class="flex items-center p-3 hover:bg-gray-100 rounded border border-gray-400 transition">
                    <div class="p-2 bg-rose-100 text-rose-600 rounded mr-3"><i class="fas fa-table"></i></div>
                    <div>
                        <p class="font-medium text-gray-800">Perfil social</p>
                        <p class="text-xs text-gray-1000">Mapa de calor y comparación demográfica por estrato.</p>
                    </div>
                </a>
            </li>

            <?php if ($dashIsSuperAdmin): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/admin/usuarios" class="flex items-center p-3 hover:bg-gray-100 rounded border border-gray-400 transition">
                        <div class="p-2 bg-blue-100 text-blue-500 rounded mr-3"><i class="fas fa-user-plus"></i></div>
                        <div>
                            <p class="font-medium text-gray-800">Añadir nuevo usuario</p>
                            <p class="text-xs text-gray-1000">Gestionar permisos de acceso al dashboard.</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/admin/catalogos" class="flex items-center p-3 hover:bg-gray-100 rounded border border-gray-400 transition">
                        <div class="p-2 bg-purple-100 text-purple-500 rounded mr-3"><i class="fas fa-edit"></i></div>
                        <div>
                            <p class="font-medium text-gray-800">Modificar Catálogos</p>
                            <p class="text-xs text-gray-1000">Añade o edita las opciones del formulario socioeconómico.</p>
                        </div>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
