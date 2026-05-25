<div class="card">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-2 border-b">5. Datos Familiares</h2>

    <!-- DATOS DEL PADRE -->
    <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-4">Datos del Padre </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <label for="nivel_educacion_padre_id" class="label-field">Nivel de Educación <span class="text-primary2-500">*</span></label>
            <select id="nivel_educacion_padre_id" name="nivel_educacion_padre_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['nivel_educacion_padre_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['nivel_educacion'])): ?>
                    <?php foreach ($catalogos['nivel_educacion'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['nivel_educacion_padre_id']) && $old['nivel_educacion_padre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label class="label-field">¿Trabaja?<span class="text-primary2-500">*</span></label>
            <div class="flex gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="padre_trabaja" value="1" required
                        <?php echo (isset($old['padre_trabaja']) && $old['padre_trabaja'] == '1') ? 'checked' : ''; ?>
                        class="mr-2 active:bg-pryimary2-500">
                    Sí
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="padre_trabaja" value="0"
                        <?php echo (isset($old['padre_trabaja']) && $old['padre_trabaja'] == '0') ? 'checked' : ''; ?>
                        class="mr-2">
                    No
                </label>
            </div>
        </div>

        <div>
            <label for="tipo_empresa_padre_id" class="label-field">Tipo de Empresa <span class="text-primary2-500">*</span></label>
            <select id="tipo_empresa_padre_id" name="tipo_empresa_padre_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['tipo_empresa_padre_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['tipo_empresa'])): ?>
                    <?php foreach ($catalogos['tipo_empresa'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['tipo_empresa_padre_id']) && $old['tipo_empresa_padre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="categoria_ocupacional_padre_id" class="label-field">Categoría Ocupacional <span class="text-primary2-500">*</span></label>
            <select id="categoria_ocupacional_padre_id" name="categoria_ocupacional_padre_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['categoria_ocupacional_padre_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['categoria_ocupacional'])): ?>
                    <?php foreach ($catalogos['categoria_ocupacional'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['categoria_ocupacional_padre_id']) && $old['categoria_ocupacional_padre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="sector_trabajo_padre_id" class="label-field">Sector de Trabajo <span class="text-primary2-500">*</span></label>
            <select id="sector_trabajo_padre_id" name="sector_trabajo_padre_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['sector_trabajo_padre_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['sector_trabajo'])): ?>
                    <?php foreach ($catalogos['sector_trabajo'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['sector_trabajo_padre_id']) && $old['sector_trabajo_padre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>


        <article class="flex justify-between">
            <div>
                <label class="label-field">¿Está en Venezuela?<span class="text-primary2-500">*</span></label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="padre_en_venezuela" value="1" required
                            <?php echo (isset($old['padre_en_venezuela']) && $old['padre_en_venezuela'] == '1') ? 'checked' : ''; ?>
                            class="mr-2">
                        Sí
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="padre_en_venezuela" value="0"
                            <?php echo (isset($old['padre_en_venezuela']) && $old['padre_en_venezuela'] == '0') ? 'checked' : ''; ?>
                            class="mr-2">
                        No
                    </label>
                </div>
            </div>

            <div>
                <label class="label-field">¿Es egresado del IUJO?<span class="text-primary2-500">*</span></label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="padre_egresado_iujo" value="1" required
                            <?php echo (isset($old['padre_egresado_iujo']) && $old['padre_egresado_iujo'] == '1') ? 'checked' : ''; ?>
                            class="mr-2">
                        Sí
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="padre_egresado_iujo" value="0"
                            <?php echo (isset($old['padre_egresado_iujo']) && $old['padre_egresado_iujo'] == '0') ? 'checked' : ''; ?>
                            class="mr-2">
                        No
                    </label>
                </div>
            </div>
        </article>
    </div>

    <!-- DATOS DE LA MADRE -->
    <h3 class="text-xl font-semibold text-gray-700 mb-3 mt-6">Datos de la Madre</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="nivel_educacion_madre_id" class="label-field">Nivel de Educación <span class="text-primary2-500">*</span></label>
            <select id="nivel_educacion_madre_id" name="nivel_educacion_madre_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['nivel_educacion_madre_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['nivel_educacion'])): ?>
                    <?php foreach ($catalogos['nivel_educacion'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['nivel_educacion_madre_id']) && $old['nivel_educacion_madre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label class="label-field">¿Trabaja? <span class="text-primary2-500">*</span></label>
            <div class="flex gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="madre_trabaja" value="1" required
                        <?php echo (isset($old['madre_trabaja']) && $old['madre_trabaja'] == '1') ? 'checked' : ''; ?>
                        class="mr-2">
                    Sí
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="madre_trabaja" value="0"
                        <?php echo (isset($old['madre_trabaja']) && $old['madre_trabaja'] == '0') ? 'checked' : ''; ?>
                        class="mr-2">
                    No
                </label>
            </div>
        </div>

        <div>
            <label for="tipo_empresa_madre_id" class="label-field">Tipo de Empresa <span class="text-primary2-500">*</span></label>
            <select id="tipo_empresa_madre_id" name="tipo_empresa_madre_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['tipo_empresa_madre_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['tipo_empresa'])): ?>
                    <?php foreach ($catalogos['tipo_empresa'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['tipo_empresa_madre_id']) && $old['tipo_empresa_madre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="categoria_ocupacional_madre_id" class="label-field">Categoría Ocupacional <span class="text-primary2-500">*</span></label>
            <select id="categoria_ocupacional_madre_id" name="categoria_ocupacional_madre_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['categoria_ocupacional_madre_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['categoria_ocupacional'])): ?>
                    <?php foreach ($catalogos['categoria_ocupacional'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['categoria_ocupacional_madre_id']) && $old['categoria_ocupacional_madre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="sector_trabajo_madre_id" class="label-field">Sector de Trabajo <span class="text-primary2-500">*</span></label>
            <select id="sector_trabajo_madre_id" name="sector_trabajo_madre_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['sector_trabajo_madre_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['sector_trabajo'])): ?>
                    <?php foreach ($catalogos['sector_trabajo'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['sector_trabajo_madre_id']) && $old['sector_trabajo_madre_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <article class="flex justify-between">


            <div>
                <label class="label-field">¿Está en Venezuela?<span class="text-primary2-500">*</span></label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="madre_en_venezuela" value="1" required
                            <?php echo (isset($old['madre_en_venezuela']) && $old['madre_en_venezuela'] == '1') ? 'checked' : ''; ?>
                            class="mr-2">
                        Sí
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="madre_en_venezuela" value="0"
                            <?php echo (isset($old['madre_en_venezuela']) && $old['madre_en_venezuela'] == '0') ? 'checked' : ''; ?>
                            class="mr-2">
                        No
                    </label>
                </div>
            </div>

            <div>
                <label class="label-field">¿Es egresada del IUJO? <span class="text-primary2-500">*</span></label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="madre_egresada_iujo" value="1" required
                            <?php echo (isset($old['madre_egresada_iujo']) && $old['madre_egresada_iujo'] == '1') ? 'checked' : ''; ?>
                            class="mr-2">
                        Sí
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="madre_egresada_iujo" value="0"
                            <?php echo (isset($old['madre_egresada_iujo']) && $old['madre_egresada_iujo'] == '0') ? 'checked' : ''; ?>
                            class="mr-2">
                        No
                    </label>
                </div>
            </div>
        </article>

    </div>
</div>