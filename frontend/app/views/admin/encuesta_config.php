<div class="bg-white rounded-lg shadow-sm border border-gray-400 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Estado de Encuestas por Instituto</h3>
    </div>

    <?php
    $institutos = isset($institutos) && is_array($institutos) ? $institutos : [];
    ?>

    <?php if (isset($apiError) && is_array($apiError) && !empty($apiError['message'])): ?>
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?php echo htmlspecialchars((string)$apiError['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($flash) && is_array($flash) && !empty($flash['message'])): ?>
        <div class="mb-4 rounded-md border border-<?php echo $flash['type'] === 'error' ? 'red' : 'green'; ?>-200 bg-<?php echo $flash['type'] === 'error' ? 'red' : 'green'; ?>-50 px-4 py-3 text-sm text-<?php echo $flash['type'] === 'error' ? 'red' : 'green'; ?>-700">
            <?php echo htmlspecialchars((string)$flash['message']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($institutos)): ?>
        <div class="space-y-4">
            <?php foreach ($institutos as $inst): ?>
                <?php
                $id = isset($inst['id']) ? (int)$inst['id'] : 0;
                $nombre = isset($inst['nombre']) ? (string)$inst['nombre'] : '';
                $siglas = isset($inst['siglas']) ? (string)$inst['siglas'] : '';
                $activa = isset($inst['encuesta_activa']) ? (bool)$inst['encuesta_activa'] : true;
                ?>
                <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 bg-gray-50">
                    <div>
                        <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($nombre); ?></h4>
                        <span class="text-sm text-gray-500"><?php echo htmlspecialchars($siglas); ?></span>
                    </div>
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/configuracion-encuestas/toggle">
                        <input type="hidden" name="instituto_id" value="<?php echo $id; ?>">
                        <button type="submit"
                            class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none <?php echo $activa ? 'bg-green-500' : 'bg-gray-300'; ?>"
                            title="<?php echo $activa ? 'Desactivar encuestas' : 'Activar encuestas'; ?>">
                            <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out <?php echo $activa ? 'translate-x-5' : 'translate-x-0'; ?>">
                            </span>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500 text-center py-8">No hay institutos disponibles.</p>
    <?php endif; ?>
</div>
