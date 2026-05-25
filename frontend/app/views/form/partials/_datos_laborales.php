<section class="card">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-2 border-b">3. Datos Laborales</h2>

    <article class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="condicion_laboral_id" class="label-field">Condición Laboral <span class="text-primary2-500">*</span></label>
            <select id="condicion_laboral_id" name="condicion_laboral_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['condicion_laboral'])): ?>
                    <?php foreach ($catalogos['condicion_laboral'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['condicion_laboral_id']) && $old['condicion_laboral_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="relacion_laboral_id" class="label-field">Relación Laboral <span class="text-primary2-500">*</span></label>
            <select id="relacion_laboral_id" name="relacion_laboral_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['relacion_laboral'])): ?>
                    <?php foreach ($catalogos['relacion_laboral'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['relacion_laboral_id']) && $old['relacion_laboral_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="tipo_organizacion_id" class="label-field">Tipo de Organización <span class="text-primary2-500">*</span></label>
            <select id="tipo_organizacion_id" name="tipo_organizacion_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['tipo_organizacion'])): ?>
                    <?php foreach ($catalogos['tipo_organizacion'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['tipo_organizacion_id']) && $old['tipo_organizacion_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="sector_trabajo_id" class="label-field">Sector de Trabajo <span class="text-primary2-500">*</span></label>
            <select id="sector_trabajo_id" name="sector_trabajo_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['sector_trabajo'])): ?>
                    <?php foreach ($catalogos['sector_trabajo'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['sector_trabajo_id']) && $old['sector_trabajo_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="categoria_ocupacional_id" class="label-field">Categoría Ocupacional <span class="text-primary2-500">*</span></label>
            <select id="categoria_ocupacional_id" name="categoria_ocupacional_id" class="input-field" required>
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['categoria_ocupacional'])): ?>
                    <?php foreach ($catalogos['categoria_ocupacional'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['categoria_ocupacional_id']) && $old['categoria_ocupacional_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </article>
</section>