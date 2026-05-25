<div class="card">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-2 border-b">6. Datos Económicos</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="transporte_id" class="label-field">Transporte <span class="text-red-500">*</span></label>
            <select id="transporte_id" name="transporte_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['transporte_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['transporte'])): ?>
                    <?php foreach ($catalogos['transporte'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['transporte_id']) && $old['transporte_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="dependencia_economica_id" class="label-field">Dependencia Económica <span class="text-red-500">*</span></label>
            <select id="dependencia_economica_id" name="dependencia_economica_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['dependencia_economica_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['dependencia_economica'])): ?>
                    <?php foreach ($catalogos['dependencia_economica'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['dependencia_economica_id']) && $old['dependencia_economica_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="fuente_ingreso_id" class="label-field">Fuente de Ingreso Familiar <span class="text-red-500">*</span></label>
            <select id="fuente_ingreso_id" name="fuente_ingreso_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['fuente_ingreso_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['fuente_ingreso'])): ?>
                    <?php foreach ($catalogos['fuente_ingreso'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['fuente_ingreso_id']) && $old['fuente_ingreso_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="ingreso_familiar_id" class="label-field">Ingreso Familiar <span class="text-red-500">*</span></label>
            <select id="ingreso_familiar_id" name="ingreso_familiar_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['ingreso_familiar_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['ingreso_familiar'])): ?>
                    <?php foreach ($catalogos['ingreso_familiar'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['ingreso_familiar_id']) && $old['ingreso_familiar_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="veracidad_id" class="label-field">Veracidad de la Información <span class="text-red-500">*</span></label>
            <select id="veracidad_id" name="veracidad_id" required class="input-field">
                <option value="" disabled <?php echo empty($old['veracidad_id']) ? 'selected' : ''; ?>>Seleccione...</option>
                <?php if (isset($catalogos['veracidad'])): ?>
                    <?php foreach ($catalogos['veracidad'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['veracidad_id']) && $old['veracidad_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

    </div>
</div>