// familia.js - Lógica específica para la sección de Datos Familiares

document.addEventListener('DOMContentLoaded', function () {

    function findOptionIndexByKeywords(select, keywords) {
        if (!select) return -1;

        for (let i = 0; i < select.options.length; i++) {
            const optionText = (select.options[i].text || '').toLowerCase();
            const matches = keywords.some(keyword => optionText.includes(keyword));
            if (matches) {
                return i;
            }
        }

        return -1;
    }

    // Función reutilizable para manejar la lógica de deshabilitación y selección de "No aplica"
    function toggleEmploymentSelects(isWorking, selectsArray) {
        if (!isWorking) {
            selectsArray.forEach(select => {
                if (select) {
                    // Buscar la opción "No trabaja", "No aplica", "Ninguna" o similar
                    for (let i = 0; i < select.options.length; i++) {
                        const optionText = select.options[i].text.toLowerCase();
                        if (optionText.includes('no trabaja') ||
                            optionText.includes('no aplica') ||
                            optionText.includes('ningun') ||
                            optionText.includes('ningún')) {
                            select.options[i].style.display = ''; // Mostrarla por si estaba oculta
                            select.selectedIndex = i;
                            break;
                        }
                    }
                    // Deshabilitar el select y agregar estilo visual
                    select.disabled = true;
                    select.classList.add('bg-gray-100', 'cursor-not-allowed', 'opacity-70');
                }
            });
        } else {
            // Habilitar los selects si la opción indica que trabaja o no es solo estudio
            selectsArray.forEach(select => {
                if (select) {
                    select.disabled = false;
                    select.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-70');

                    // Ocultar la opción "No trabaja", "No aplica", etc.
                    for (let i = 0; i < select.options.length; i++) {
                        const optionText = select.options[i].text.toLowerCase();
                        if (optionText.includes('no trabaja') ||
                            optionText.includes('no aplica') ||
                            optionText.includes('ningun') ||
                            optionText.includes('ningún')) {
                            select.options[i].style.display = 'none';
                            // Si estaba seleccionada, limpiar la selección para forzar a que elija una válida
                            if (select.selectedIndex === i) {
                                select.selectedIndex = 0;
                            }
                        } else {
                            select.options[i].style.display = ''; // Asegurar que el resto se muestre
                        }
                    }
                }
            });
        }
    }

    // ---------- LÓGICA DEL PADRE ----------
    const padreTrabajaRadios = document.querySelectorAll('input[name="padre_trabaja"]');
    const padreDependentSelects = [
        document.getElementById('tipo_empresa_padre_id'),
        document.getElementById('categoria_ocupacional_padre_id'),
        document.getElementById('sector_trabajo_padre_id')
    ];

    function handlePadreTrabajaChange() {
        const selectedRadio = document.querySelector('input[name="padre_trabaja"]:checked');
        if (selectedRadio) {
            const isWorking = selectedRadio.value === '1'; // 1 = Sí, 0 = No
            toggleEmploymentSelects(isWorking, padreDependentSelects);
        }
    }

    // Agregar listeners al padre
    padreTrabajaRadios.forEach(radio => {
        radio.addEventListener('change', handlePadreTrabajaChange);
    });

    // Validar estado inicial padre
    if (padreTrabajaRadios.length > 0) handlePadreTrabajaChange();


    // ---------- LÓGICA DE LA MADRE ----------
    const madreTrabajaRadios = document.querySelectorAll('input[name="madre_trabaja"]');
    const madreDependentSelects = [
        document.getElementById('tipo_empresa_madre_id'),
        document.getElementById('categoria_ocupacional_madre_id'),
        document.getElementById('sector_trabajo_madre_id')
    ];

    function handleMadreTrabajaChange() {
        const selectedRadio = document.querySelector('input[name="madre_trabaja"]:checked');
        if (selectedRadio) {
            const isWorking = selectedRadio.value === '1'; // 1 = Sí, 0 = No
            toggleEmploymentSelects(isWorking, madreDependentSelects);
        }
    }

    // Agregar listeners a la madre
    madreTrabajaRadios.forEach(radio => {
        radio.addEventListener('change', handleMadreTrabajaChange);
    });

    // Validar estado inicial madre
    if (madreTrabajaRadios.length > 0) handleMadreTrabajaChange();


    // ---------- HABILITAR AL ENVIAR ----------
    // Antes de enviar el formulario, habilitar los selects para que sus valores se pasen en el POST
    const form = document.getElementById('socioeconomicForm');
    if (form) {
        form.addEventListener('submit', function () {
            const allFamilySelects = [...padreDependentSelects, ...madreDependentSelects];
            allFamilySelects.forEach(select => {
                if (select) {
                    select.disabled = false;
                }
            });

            padreTrabajaRadios.forEach(radio => {
                radio.disabled = false;
            });

            madreTrabajaRadios.forEach(radio => {
                radio.disabled = false;
            });
        });
    }
});
