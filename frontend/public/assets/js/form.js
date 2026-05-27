// form.js - Interactividad del formulario socioeconómico

document.addEventListener('DOMContentLoaded', function () {

    let bypassSubmitValidation = false;

    const pad = value => String(value).padStart(2, '0');
    const toDateValue = date => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

    function getFechaLimites() {
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 15, today.getMonth(), today.getDate());
        const minDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
        return {
            maxDate,
            minDate,
            max: toDateValue(maxDate),
            min: toDateValue(minDate),
        };
    }

    function parseDateValue(value) {
        if (!/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(value)) return null;
        const parts = value.split('-').map(Number);
        if (parts.length !== 3) return null;
        const year = parts[0];
        const month = parts[1] - 1;
        const day = parts[2];
        const date = new Date(year, month, day);
        if (Number.isNaN(date.getTime())) return null;
        if (date.getFullYear() !== year || date.getMonth() !== month || date.getDate() !== day) {
            return null;
        }
        return date;
    }

    function validateFechaNacimientoInput(fechaNacimientoEl) {
        if (!fechaNacimientoEl) return false;

        const fechaVal = fechaNacimientoEl.value;
        if (!fechaVal) {
            fechaNacimientoEl.setCustomValidity('Este campo es obligatorio.');
            return false;
        }

        const limites = getFechaLimites();
        const fechaDate = parseDateValue(fechaVal);
        if (!fechaDate) {
            fechaNacimientoEl.setCustomValidity('Fecha de nacimiento no válida.');
            return false;
        }

        if (fechaDate > limites.maxDate) {
            fechaNacimientoEl.setCustomValidity('Debe ser mayor de 14 años.');
            return false;
        }

        if (fechaDate < limites.minDate) {
            fechaNacimientoEl.setCustomValidity('Fecha de nacimiento no válida.');
            return false;
        }

        fechaNacimientoEl.setCustomValidity('');
        return true;
    }

    const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
    if (fechaNacimientoInput) {
        const limites = getFechaLimites();
        fechaNacimientoInput.setAttribute('max', limites.max);
        fechaNacimientoInput.setAttribute('min', limites.min);
        fechaNacimientoInput.addEventListener('change', function () {
            validateFechaNacimientoInput(this);
        });
        fechaNacimientoInput.addEventListener('blur', function () {
            validateFechaNacimientoInput(this);
        });
        fechaNacimientoInput.addEventListener('input', function () {
            validateFechaNacimientoInput(this);
        });
    }

    const selects = document.querySelectorAll('select');

    selects.forEach(select => {
        select.addEventListener('change', function () {
            if (this.value) {
                this.classList.remove('border-red-500');

                // Deshabilita y oculta la opción "Seleccione..." por defecto
                const defaultOption = this.querySelector('option[value=""]');
                if (defaultOption) {
                    defaultOption.disabled = true;
                    defaultOption.hidden = true;
                }
            }
        });
    });

    // ===== MOSTRAR/OCULTAR NÚMERO DE HIJOS EN EL FORMULAIRO =====
    const hijosRadios = document.querySelectorAll('input[name="hijos"]');
    const numeroHijosContainer = document.getElementById('numero_hijos_container');

    if (hijosRadios.length > 0 && numeroHijosContainer) {
        hijosRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === '1') {
                    numeroHijosContainer.style.display = 'block';
                } else {
                    numeroHijosContainer.style.display = 'none';
                    document.getElementById('numero_hijos').value = '0';
                }
            });
        });

        // Inicializar al cargar
        const hijosChecked = document.querySelector('input[name="hijos"]:checked');
        if (hijosChecked && hijosChecked.value === '1') {
            numeroHijosContainer.style.display = 'block';
        }
    }

    // ===== VALIDACIÓN DE ARCHIVO DE CÉDULA =====
    const cedulaFile = document.getElementById('foto_cedula');
    const cedulaFileName = document.getElementById('foto_cedula_filename');
    if (cedulaFile) {
        cedulaFile.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                // Validar tamaño (5MB máximo)
                const maxSize = 5 * 1024 * 1024; // 5MB en bytes
                if (file.size > maxSize) {
                    showAlertModal('El archivo es demasiado grande. El tamaño máximo es 5MB.');
                    this.value = '';
                    if (cedulaFileName) {
                        cedulaFileName.textContent = 'Ningún archivo seleccionado';
                    }
                    return;
                }

                // Validar tipo de archivo
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showAlertModal('Tipo de archivo no permitido. Solo se aceptan JPG, PNG y WEBP.');
                    this.value = '';
                    if (cedulaFileName) {
                        cedulaFileName.textContent = 'Ningún archivo seleccionado';
                    }
                    return;
                }

                if (cedulaFileName) {
                    cedulaFileName.textContent = file.name;
                }
            } else if (cedulaFileName) {
                cedulaFileName.textContent = 'Ningún archivo seleccionado';
            }
        });
    }

    // ===== SMOOTH SCROLL PARA ERRORES =====
    const errorAlert = document.querySelector('.bg-red-100');
    if (errorAlert) {
        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // ===== CONFIRMACIÓN ANTES DE ENVIAR =====
    const form = document.querySelector('form');

    async function validarDuplicadosPaso1(stepElement) {
        const formEl = document.getElementById('socioeconomicForm');
        const apiBaseUrl = formEl ? (formEl.dataset.apiBaseUrl || '').replace(/\/+$/, '') : '';
        const checkUrl = formEl ? (formEl.dataset.checkUrl || '').trim() : '';
        const root = stepElement || document;
        const cedulaEl = root.querySelector('#cedula');
        const emailEl = root.querySelector('#email');

        if ((!checkUrl && !apiBaseUrl) || (!cedulaEl && !emailEl)) {
            return true;
        }

        const cedula = cedulaEl ? (cedulaEl.value || '').trim() : '';
        const email = emailEl ? (emailEl.value || '').trim() : '';

        if (cedula === '' && email === '') {
            return true;
        }

        try {
            const qs = new URLSearchParams();
            if (cedula !== '') qs.set('cedula', cedula);
            if (email !== '') qs.set('email', email);

            const endpoint = checkUrl || (apiBaseUrl + '/encuesta/check');

            const resp = await fetch(endpoint + '?' + qs.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
            });

            const payload = await resp.json().catch(() => null);

            if (cedulaEl) cedulaEl.setCustomValidity('');
            if (emailEl) emailEl.setCustomValidity('');

            if (!resp.ok || !payload || payload.success !== true || !payload.data) {
                showAlertModal('No se pudo validar cédula/correo en este momento. Intente nuevamente.');
                return false;
            }

            const cedulaExists = !!payload.data.cedula_exists;
            const emailExists = !!payload.data.email_exists;

            if (cedulaExists && cedulaEl) {
                cedulaEl.setCustomValidity('Ya existe una encuesta registrada con esta cédula.');
                cedulaEl.reportValidity();
                cedulaEl.focus();
                return false;
            }

            if (emailExists && emailEl) {
                emailEl.setCustomValidity('Ya existe una encuesta registrada con este correo.');
                emailEl.reportValidity();
                emailEl.focus();
                return false;
            }

            return true;
        } catch (e) {
            showAlertModal('No se pudo validar cédula/correo por un problema de conexión. Intente nuevamente.');
            return false;
        }
    }

    if (form) {
        form.addEventListener('submit', async function (e) {
            if (bypassSubmitValidation) {
                bypassSubmitValidation = false;
                return;
            }

            e.preventDefault();

            const surveyStartDateInput = document.getElementById('survey-start-date');
            if (surveyStartDateInput && !surveyStartDateInput.value) {
                surveyStartDateInput.value = new Date().toISOString().slice(0, 19).replace('T', ' ');
            }

            const activeStep = document.querySelector('.form-step:not(.hidden)');
            if (activeStep && !validateStep(activeStep)) {
                return false;
            }

            const step1 = document.getElementById('step-1');
            const duplicadosValidos = await validarDuplicadosPaso1(step1);
            if (!duplicadosValidos) {
                return false;
            }

            const veracidad = document.getElementById('veracidad_id');
            if (veracidad && !veracidad.value) {
                showAlertModal('Por favor, confirme la veracidad de la información antes de enviar.');
                veracidad.focus();
                return false;
            }

            // Confirmación final
            const confirmacion = await showConfirmModal('¿Está seguro de que desea enviar el formulario? Verifique que todos los datos sean correctos.');
            if (!confirmacion) {
                return false;
            }

            const formData = new FormData(form);

            bypassSubmitValidation = true;
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    }

    // ===== GESTIÓN DE PASOS DEL FORMULARIO =====
    const steps = document.querySelectorAll('.form-step');
    const progressBar = document.getElementById('progressBar');
    const testOnlyStepId = form ? (form.dataset.testOnlyStep || '').trim() : '';
    const isTestOnlyStepMode = testOnlyStepId !== '';

    // Función para mostrar un paso específico y ocultar los demás
    function showStep(stepId) {
        steps.forEach(step => {
            if (step.id === stepId) {
                step.classList.remove('hidden');
            } else {
                step.classList.add('hidden');
            }
        });

        // Actualizar barra de progreso
        const stepNumber = parseInt(stepId.replace('step-', ''));
        const totalSteps = steps.length;
        const progress = (stepNumber / totalSteps) * 100;

        if (progressBar) {
            progressBar.style.width = `${progress}%`;
            // progressBar.innerText = `Paso ${stepNumber} de ${totalSteps}`;
        }

        // Scroll arriba
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Función para validar campos del paso actual
    function validateStep(stepElement) {
        // Seleccionamos solo los inputs visibles y habilitados para validar
        const inputs = stepElement.querySelectorAll('input, select, textarea');
        let isValid = true;
        let firstInvalidInput = null;

        // Validación especial: teléfono puede ser 11 dígitos completo o 7 dígitos + prefijo.
        const telefonoEl = stepElement.querySelector('#telefono');
        const prefijoEl = stepElement.querySelector('#prefijo');
        if (telefonoEl) {
            const telefonoVal = (telefonoEl.value || '').replace(/[^0-9]/g, '');
            const prefijoVal = prefijoEl ? (prefijoEl.value || '').replace(/[^0-9]/g, '') : '';

            if (telefonoVal.length === 7 && prefijoVal.length !== 4) {
                telefonoEl.setCustomValidity('Seleccione un prefijo (0414/0416/0424/0426) o escriba el teléfono completo de 11 dígitos.');
            } else {
                telefonoEl.setCustomValidity('');
            }
        }

        inputs.forEach(input => {
            if (input.id === 'fecha_nacimiento') {
                const fechaValida = validateFechaNacimientoInput(input);
                if (!fechaValida) {
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = input;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
                return;
            }

            // Check validity solo devuelve false si tiene restricciones (required, pattern, etc) que no se cumplen
            if (!input.checkValidity()) {
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = input;

                // Resaltar visualmente
                input.classList.add('border-red-500');
                // input.classList.add('ring-2', 'ring-red-500'); // Optional: more visibility
            } else {
                input.classList.remove('border-red-500');
                // input.classList.remove('ring-2', 'ring-red-500');
            }
        });

        if (firstInvalidInput) {
            firstInvalidInput.reportValidity(); // Muestra el mensaje nativo solo del primero
            firstInvalidInput.focus();
        }

        return isValid;
    }

    // Inicializar visualización de pasos
    // Ocultar todos menos el primero si no se ha hecho ya
    if (isTestOnlyStepMode) {
        showStep(testOnlyStepId);

        document.querySelectorAll('.next-step, .prev-step').forEach(button => {
            button.classList.add('hidden');
        });

        const progressSection = progressBar ? progressBar.closest('section') : null;
        if (progressSection) {
            progressSection.classList.add('hidden');
        }
    } else {
        steps.forEach((step, index) => {
            if (index === 0) { // Índice 0 corresponde a la sección 1
                step.classList.remove('hidden');
            } else {
                step.classList.add('hidden');
            }
        });
    }

    // Actualizar barra de progreso para iniciar en el paso 1
    if (progressBar && steps.length > 0 && !isTestOnlyStepMode) {
        progressBar.style.width = `${(1 / steps.length) * 100}%`;
    }

    // Event Listeners para botones Siguiente
    document.querySelectorAll('.next-step').forEach(button => {
        button.addEventListener('click', async function () {
            // Buscamos el contenedor del paso actual
            // Usamos closest('.form-step') para asegurarnos de obtener el padre correcto
            const currentStep = this.closest('.form-step');
            const nextStepId = this.dataset.next;

            if (!currentStep || !validateStep(currentStep)) {
                return;
            }

            // Chequeo temprano: en el paso 1 validar duplicados de cédula/email.
            if (currentStep.id === 'step-1') {
                const prevDisabled = this.disabled;
                this.disabled = true;

                try {
                    const duplicadosValidos = await validarDuplicadosPaso1(currentStep);
                    if (!duplicadosValidos) {
                        return;
                    }
                } finally {
                    this.disabled = prevDisabled;
                }
            }

            showStep(nextStepId);
        });
    });

    // Event Listeners para botones Atrás
    document.querySelectorAll('.prev-step').forEach(button => {

        button.addEventListener('click', function () {
            const prevStepId = this.dataset.prev;
            showStep(prevStepId);
        });
    });

    // Limpiar validaciones personalizadas (duplicados) al editar
    const cedulaInput = document.getElementById('cedula');
    if (cedulaInput) {
        cedulaInput.addEventListener('input', function () {
            this.setCustomValidity('');
        });
    }

    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            this.setCustomValidity('');
        });
    }
});
