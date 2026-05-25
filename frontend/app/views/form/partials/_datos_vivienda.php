<section class="card">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-2 border-b">4. Datos de Vivienda</h2>

    <article class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="tipo_convivencia_id" class="label-field">Tipo de Convivencia <span class="text-primary2-500">*</span></label>
            <select id="tipo_convivencia_id" name="tipo_convivencia_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['tipo_convivencia'])): ?>
                    <?php foreach ($catalogos['tipo_convivencia'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['tipo_convivencia_id']) && $old['tipo_convivencia_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="tipo_vivienda_id" class="label-field">Tipo de Vivienda <span class="text-primary2-500">*</span></label>
            <select id="tipo_vivienda_id" name="tipo_vivienda_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['tipo_vivienda'])): ?>
                    <?php foreach ($catalogos['tipo_vivienda'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['tipo_vivienda_id']) && $old['tipo_vivienda_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="tenencia_vivienda_id" class="label-field">Tenencia de Vivienda</label>
            <select id="tenencia_vivienda_id" name="tenencia_vivienda_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['tenencia_vivienda'])): ?>
                    <?php foreach ($catalogos['tenencia_vivienda'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['tenencia_vivienda_id']) && $old['tenencia_vivienda_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="numero_habitantes" class="label-field">Número de Habitantes <span class="text-primary2-500">*</span></label>
            <input type="number" id="numero_habitantes" name="numero_habitantes" min="1"
                value="<?php echo isset($old['numero_habitantes']) ? htmlspecialchars($old['numero_habitantes']) : ''; ?>"
                class="input-field" required>
        </div>

        <div>
            <label for="numero_ocupantes_familia" class="label-field">Número de Ocupantes de la Familia <span class="text-primary2-500">*</span></label>
            <input type="number" id="numero_ocupantes_familia" name="numero_ocupantes_familia" min="1"
                value="<?php echo isset($old['numero_ocupantes_familia']) ? htmlspecialchars($old['numero_ocupantes_familia']) : ''; ?>"
                class="input-field" required>
        </div>

        <!-- Ambientes de Vivienda (Checkboxes) -->
        <?php
        extract([
            'label' => 'Ambientes de la Vivienda',
            'name' => 'ambientes_vivienda[]',
            'options' => isset($catalogos['ambiente_vivienda']) ? $catalogos['ambiente_vivienda'] : [],
            'oldData' => isset($old['ambientes_vivienda']) ? $old['ambientes_vivienda'] : []
        ]);
        include __DIR__ . '/../components/_checkbox_group.php';
        ?>

        <!-- Activos de Vivienda (Checkboxes) -->
        <?php
        extract([
            'label' => 'Activos de la Vivienda',
            'name' => 'activos_vivienda[]',
            'options' => isset($catalogos['activo_vivienda']) ? $catalogos['activo_vivienda'] : [],
            'oldData' => isset($old['activos_vivienda']) ? $old['activos_vivienda'] : []
        ]);
        include __DIR__ . '/../components/_checkbox_group.php';
        ?>

        <!-- Servicios de Vivienda (Checkboxes) -->
        <?php
        extract([
            'label' => 'Servicios de la Vivienda',
            'name' => 'servicios_vivienda[]',
            'options' => isset($catalogos['servicio_vivienda']) ? $catalogos['servicio_vivienda'] : [],
            'oldData' => isset($old['servicios_vivienda']) ? $old['servicios_vivienda'] : []
        ]);
        include __DIR__ . '/../components/_checkbox_group.php';
        ?>

        <!-- Frecuencias de Servicios -->
        <div>
            <label for="frecuencia_agua_id" class="label-field">Frecuencia Servicio de Agua <span class="text-primary2-500">*</span></label>
            <select id="frecuencia_agua_id" name="frecuencia_agua_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['frecuencia_agua'])): ?>
                    <?php foreach ($catalogos['frecuencia_agua'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['frecuencia_agua_id']) && $old['frecuencia_agua_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="frecuencia_aseo_id" class="label-field">Frecuencia Servicio de Aseo</label>
            <select id="frecuencia_aseo_id" name="frecuencia_aseo_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['frecuencia_aseo'])): ?>
                    <?php foreach ($catalogos['frecuencia_aseo'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['frecuencia_aseo_id']) && $old['frecuencia_aseo_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="frecuencia_electricidad_id" class="label-field">Frecuencia Servicio de Electricidad <span class="text-primary2-500">*</span></label>
            <select id="frecuencia_electricidad_id" name="frecuencia_electricidad_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['frecuencia_electricidad'])): ?>
                    <?php foreach ($catalogos['frecuencia_electricidad'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['frecuencia_electricidad_id']) && $old['frecuencia_electricidad_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="frecuencia_gas_id" class="label-field">Frecuencia Servicio de Gas <span class="text-primary2-500">*</span></label>
            <select id="frecuencia_gas_id" name="frecuencia_gas_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['frecuencia_gas'])): ?>
                    <?php foreach ($catalogos['frecuencia_gas'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['frecuencia_gas_id']) && $old['frecuencia_gas_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </article>
</section>