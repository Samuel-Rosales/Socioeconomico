<div class="bg-white rounded-lg shadow-sm border border-gray-400 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Nueva Encuesta</h3>
        <a href="<?php echo BASE_URL; ?>/admin/respuestas" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <?php
    $errors = isset($errors) && is_array($errors) ? $errors : [];
    $old = isset($old) && is_array($old) ? $old : [];
    $catalogos = isset($catalogos) && is_array($catalogos) ? $catalogos : [];
    $institutoId = isset($institutoId) ? (int)$institutoId : null;
    $institutos = isset($institutos) && is_array($institutos) ? $institutos : [];
    $isSuperAdmin = isset($isSuperAdmin) ? (bool)$isSuperAdmin : false;
    ?>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <p class="font-bold mb-2">Por favor corrija los siguientes errores:</p>
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="socioeconomicForm" action="<?php echo BASE_URL; ?>/admin/encuestas/nueva" method="POST" class="space-y-6" enctype="multipart/form-data" data-api-base-url="<?php echo htmlspecialchars(API_BASE_URL); ?>" data-check-url="<?php echo htmlspecialchars(BASE_URL . '/encuesta/check'); ?>">
        <input type="hidden" name="inicio" id="survey-start-date" value="">

        <?php if ($isSuperAdmin && !empty($institutos)): ?>
        <div class="bg-blue-50 dark:bg-slate-800 border border-blue-200 dark:border-slate-600 rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-2">Instituto</label>
            <select name="instituto_id" class="border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-md p-2 focus:ring-primary-500 focus:border-primary-500 outline-none w-full max-w-md">
                <option value="">Seleccione un instituto</option>
                <?php foreach ($institutos as $inst): ?>
                    <?php
                    $instId = isset($inst['id']) ? (int)$inst['id'] : 0;
                    $instNombre = isset($inst['nombre']) ? (string)$inst['nombre'] : '';
                    $instSiglas = isset($inst['siglas']) ? (string)$inst['siglas'] : '';
                    $selected = ($institutoId !== null && $institutoId === $instId);
                    ?>
                    <option value="<?php echo $instId; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($instNombre . ' (' . $instSiglas . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php elseif ($institutoId !== null): ?>
        <input type="hidden" name="instituto_id" value="<?php echo $institutoId; ?>">
        <?php endif; ?>

        <section id="step-1" class="form-step">
            <?php include APP_PATH . '/views/form/partials/_datos_personales.php'; ?>
            <div class="flex justify-end mt-4">
                <button type="button" class="btn-primary next-step" data-next="step-2">Siguiente</button>
            </div>
        </section>

        <section id="step-2" class="form-step hidden">
            <?php include APP_PATH . '/views/form/partials/_datos_academicos.php'; ?>
            <div class="flex justify-between mt-4">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-md transition duration-200 prev-step" data-prev="step-1">Atrás</button>
                <button type="button" class="btn-primary next-step" data-next="step-3">Siguiente</button>
            </div>
        </section>

        <section id="step-3" class="form-step hidden">
            <?php include APP_PATH . '/views/form/partials/_datos_laborales.php'; ?>
            <div class="flex justify-between mt-4">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-md transition duration-200 prev-step" data-prev="step-2">Atrás</button>
                <button type="button" class="btn-primary next-step" data-next="step-4">Siguiente</button>
            </div>
        </section>

        <section id="step-4" class="form-step hidden">
            <?php include APP_PATH . '/views/form/partials/_datos_vivienda.php'; ?>
            <div class="flex justify-between mt-4">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-md transition duration-200 prev-step" data-prev="step-3">Atrás</button>
                <button type="button" class="btn-primary next-step" data-next="step-5">Siguiente</button>
            </div>
        </section>

        <section id="step-5" class="form-step hidden">
            <?php include APP_PATH . '/views/form/partials/_datos_familiares.php'; ?>
            <div class="flex justify-between mt-4">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-md transition duration-200 prev-step" data-prev="step-4">Atrás</button>
                <button type="button" class="btn-primary next-step" data-next="step-6">Siguiente</button>
            </div>
        </section>

        <section id="step-6" class="form-step hidden">
            <?php include APP_PATH . '/views/form/partials/_datos_economicos.php'; ?>
            <div class="flex justify-between mt-4 gap-4">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-md transition duration-200 prev-step" data-prev="step-5">Atrás</button>
                <div class="flex gap-4">
                    <a href="<?php echo BASE_URL; ?>/admin/respuestas" class="inline-block bg-gray-400 hover:bg-gray-500 text-white font-semibold py-2 px-6 rounded-md transition duration-200">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        Crear Encuesta
                    </button>
                </div>
            </div>
        </section>
    </form>
</div>

<script src="<?php echo BASE_URL; ?>/assets/js/custom-alerts.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/form.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/empleo.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/familia.js"></script>
