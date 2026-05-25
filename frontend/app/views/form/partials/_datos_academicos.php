<section class="card">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-2 border-b">2. Datos Académicos</h2>

    <article class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="tipo_estudiante_id" class="label-field">Tipo de Estudiante <span class="text-red-500">*</span></label>
            <select id="tipo_estudiante_id" name="tipo_estudiante_id" required class="input-field">
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['tipo_estudiante'])): ?>
                    <?php foreach ($catalogos['tipo_estudiante'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['tipo_estudiante_id']) && $old['tipo_estudiante_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="carrera_id" class="label-field">Carrera <span class="text-red-500">*</span></label>
            <select id="carrera_id" name="carrera_id" required class="input-field">
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['carrera'])): ?>
                    <?php foreach ($catalogos['carrera'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['carrera_id']) && $old['carrera_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="semestre_id" class="label-field">Semestre <span class="text-red-500">*</span></label>
            <select id="semestre_id" name="semestre_id" required class="input-field">
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['semestre'])): ?>
                    <?php foreach ($catalogos['semestre'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['semestre_id']) && $old['semestre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="tipo_beca_id" class="label-field">Tipo de Beca</label>
            <select id="tipo_beca_id" name="tipo_beca_id" class="input-field">
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['tipo_beca'])): ?>
                    <?php foreach ($catalogos['tipo_beca'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['tipo_beca_id']) && $old['tipo_beca_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

         <div>
            <label class="label-field">¿Estudió en FyA?</label>
            <div class="flex gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="estudio_fya" value="1"
                        <?php echo (isset($old['estudio_fya']) && $old['estudio_fya'] == '1') ? 'checked' : ''; ?>
                        class="mr-2">
                    Sí
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="estudio_fya" value="0"
                        <?php echo (!isset($old['estudio_fya']) || $old['estudio_fya'] == '0') ? 'checked' : ''; ?>
                        class="mr-2">
                    No
                </label>
            </div>
        </div>
    </article>
</section>