<section class="card">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-2 border-b">1. Datos Personales</h2>

    <article class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="nombres" class="label-field">Nombres <span class="text-primary2-500">*</span></label>
            <input type="text" id="nombres" name="nombres" required
                value="<?php echo isset($old['nombres']) ? htmlspecialchars($old['nombres']) : ''; ?>"
                class="input-field">
        </div>

        <div>
            <label for="apellidos" class="label-field">Apellidos <span class="text-primary2-500">*</span></label>
            <input type="text" id="apellidos" name="apellidos" required
                value="<?php echo isset($old['apellidos']) ? htmlspecialchars($old['apellidos']) : ''; ?>"
                class="input-field">
        </div>

        <div>
            <label for="cedula" class="label-field">Cédula <span class="text-primary2-500">*</span></label>
            <input type="text" pattern="^[0-9]{7,8}$" id="cedula" name="cedula" maxlength="8" required
                value="<?php echo isset($old['cedula']) ? htmlspecialchars($old['cedula']) : ''; ?>"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                class="input-field" placeholder="12344698">
        </div>

        <div>
            <label for="nacionalidad_id" class="label-field">Nacionalidad <span class="text-primary2-500">*</span></label>
            <select id="nacionalidad_id" name="nacionalidad_id" required class="input-field">
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['nacionalidad'])): ?>
                    <?php foreach ($catalogos['nacionalidad'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['nacionalidad_id']) && $old['nacionalidad_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="sexo_id" class="label-field">Sexo <span class="text-primary2-500">*</span></label>
            <select id="sexo_id" name="sexo_id" required class="input-field">
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['sexo'])): ?>
                    <?php foreach ($catalogos['sexo'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['sexo_id']) && $old['sexo_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="fecha_nacimiento" class="label-field">Fecha de Nacimiento <span class="text-primary2-500">*</span></label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required
                value="<?php echo isset($old['fecha_nacimiento']) ? htmlspecialchars($old['fecha_nacimiento']) : ''; ?>"
                class="input-field">
        </div>

        <div>
            <label for="email" class="label-field">Correo Electrónico <span class="text-primary2-500">*</span></label>
            <input type="email" id="email" name="email" required
                value="<?php echo isset($old['email']) ? htmlspecialchars($old['email']) : ''; ?>"
                class="input-field">
        </div>

        <div>
            <label for="telefono" class="label-field">Teléfono <span class="text-primary2-500">*</span></label>
            <input type="tel" id="telefono" name="telefono" required
                pattern="^(0424|0426|0422|0412|0414)[0-9]{7}$" maxlength="11"
                value="<?php echo isset($old['telefono']) ? htmlspecialchars($old['telefono']) : ''; ?>"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                class="input-field" placeholder="Ej: 04121234567">
        </div>

        <div>
            <label for="estado_civil_id" class="label-field">Estado Civil <span class="text-primary2-500">*</span></label>
            <select id="estado_civil_id" name="estado_civil_id" required class="input-field">
                <option value="">Seleccione...</option>
                <?php if (isset($catalogos['estado_civil'])): ?>
                    <?php foreach ($catalogos['estado_civil'] as $item): ?>
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo (isset($old['estado_civil_id']) && $old['estado_civil_id'] == $item['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="flex flex-col">
            <label for="foto_cedula" class="label-field">Foto de la cédula <span class="text-primary2-500">*</span></label>
            <label for="foto_cedula" class="input-field bg-gray-50 text-gray-700 hover:bg-gray-100 cursor-pointer inline-flex items-center justify-center gap-2 rounded-md border px-4 py-2 font-semibold transition duration-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                <i class="fa-solid  fa-address-card" aria-hidden="true"></i>
                <span>Selecionar imagen</span>
            </label>
            <input id="foto_cedula" class="hidden" type="file" name="foto_cedula" accept="image/*" required>
            <small id="foto_cedula_filename" class="text-xs text-gray-500 dark:text-slate-300">Ningún archivo seleccionado</small>
        </div>

        <div class="md:col-span-2">
            <label for="direccion" class="label-field">Dirección <span class="text-primary2-500">*</span></label>
            <textarea id="direccion" name="direccion" required rows="3"
                class="input-field"><?php echo isset($old['direccion']) ? htmlspecialchars($old['direccion']) : ''; ?></textarea>
        </div>

        <div>
            <label for="discapacidad" class="label-field">Discapacidad (si aplica)</label>
            <input type="text" id="discapacidad" name="discapacidad"
                value="<?php echo isset($old['discapacidad']) ? htmlspecialchars($old['discapacidad']) : ''; ?>"
                class="input-field">
        </div>

        <div>
            <label for="enfermedad_cronica" class="label-field">Enfermedad Crónica (si aplica)</label>
            <input type="text" id="enfermedad_cronica" name="enfermedad_cronica"
                value="<?php echo isset($old['enfermedad_cronica']) ? htmlspecialchars($old['enfermedad_cronica']) : ''; ?>"
                class="input-field">
        </div>

        <div>
            <label class="label-field">¿Tiene hijos?</label>
            <div class="flex gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="hijos" value="1" id="hijos_si"
                        <?php echo (isset($old['hijos']) && $old['hijos'] == '1') ? 'checked' : ''; ?>
                        class="mr-2">
                    Sí
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="hijos" value="0" id="hijos_no"
                        <?php echo (!isset($old['hijos']) || $old['hijos'] == '0') ? 'checked' : ''; ?>
                        class="mr-2">
                    No
                </label>
            </div>
        </div>

        <div id="numero_hijos_container" style="display: none;">
            <label for="numero_hijos" class="label-field">Número de Hijos</label>
            <input type="number" id="numero_hijos" name="numero_hijos" min="0"
                value="<?php echo isset($old['numero_hijos']) ? htmlspecialchars($old['numero_hijos']) : '0'; ?>"
                class="input-field">
        </div>
    </article>
</section>