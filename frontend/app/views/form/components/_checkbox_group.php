<?php

/**
 * Componente reutilizable para un grupo de checkboxes
 *
 * Variables esperadas:
 * @param string $label Título del grupo de opciones
 * @param string $name Nombre del campo para el formulario (debe incluir [] si es múltiple, ej. "ambientes_vivienda[]")
 * @param array $options Opciones a mostrar, array con formato [['id' => '1', 'nombre' => 'Sala'], ...]
 * @param array $oldData Array con los valores seleccionados previamente
 */
$label = $label ?? '';
$name = $name ?? '';
$options = $options ?? [];
$oldData = $oldData ?? [];
?>

<div class="md:col-span-2 mb-6">
    <label class="label-field text-lg font-semibold text-slate-700 block mb-4"><?php echo htmlspecialchars($label); ?></label>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mt-2">
        <?php if (!empty($options)): ?>
            <?php foreach ($options as $item): ?>
                <label class="ck-item group relative flex items-center gap-3 p-3 border-2 rounded-lg cursor-pointer transition-all duration-200 overflow-hidden">
                    <input type="checkbox"
                        name="<?php echo htmlspecialchars($name); ?>"
                        value="<?php echo $item['id']; ?>"
                        <?php echo (isset($oldData) && is_array($oldData) && in_array($item['id'], $oldData)) ? 'checked' : ''; ?>
                        class="sr-only">

                    <div class="ck-box relative shrink-0 w-5 h-5 border-2 rounded transition-all duration-200">
                        <svg class="absolute inset-0 w-full h-full p-0.5 text-white transition-all duration-200"
                            viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <span class="ck-text min-w-0 text-sm text-slate-600 font-medium select-none leading-snug truncate">
                        <?php echo htmlspecialchars($item['nombre']); ?>
                    </span>
                </label>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
