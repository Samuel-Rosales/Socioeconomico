// empleo.js - Lógica específica para la sección de Datos Laborales

document.addEventListener('DOMContentLoaded', function () {
    const condicionLaboralSelect = document.getElementById('condicion_laboral_id');
    const relacionLaboralSelect = document.getElementById('relacion_laboral_id');
    const tipoOrganizacionSelect = document.getElementById('tipo_organizacion_id');
    const sectorTrabajoSelect = document.getElementById('sector_trabajo_id');
    const categoriaOcupacionalSelect = document.getElementById('categoria_ocupacional_id');

    const dependentSelects = [
        relacionLaboralSelect,
        tipoOrganizacionSelect,
        sectorTrabajoSelect,
        categoriaOcupacionalSelect
    ];

    function handleCondicionLaboralChange() {
        if (!condicionLaboralSelect) return;

        const selectedOption = condicionLaboralSelect.options[condicionLaboralSelect.selectedIndex];
        const selectedText = selectedOption ? selectedOption.text.toLowerCase() : '';

        // Comprobar si la opción seleccionada es "estudia", "buscando trabajo" o "buscando trabajo, estudia"
        // Convertimos a minúsculas y eliminamos espacios extra para asegurar la comparación
        const normalizedText = selectedText.trim().toLowerCase();

        const noTrabaja = normalizedText === 'estudia' ||
            normalizedText === 'buscando trabajo' ||
            normalizedText === 'buscando trabajo, estudia';

        if (noTrabaja) {
            dependentSelects.forEach(select => {
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
        } else if (selectedText.trim() !== '' && !selectedText.includes('seleccione')) {
            // Habilitar los selects si la opción indica que trabaja o no es solo estudio
            dependentSelects.forEach(select => {
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
        } else {
            // Estado inicial ("Seleccione...")
            dependentSelects.forEach(select => {
                if (select) {
                    select.disabled = false;
                    select.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-70');
                    // Restaurar visualización de todas las opciones
                    for (let i = 0; i < select.options.length; i++) {
                        select.options[i].style.display = '';
                    }
                }
            });
        }
    }

    if (condicionLaboralSelect) {
        condicionLaboralSelect.addEventListener('change', handleCondicionLaboralChange);
        // Ejecutar una vez al inicio para establecer el estado inicial
        handleCondicionLaboralChange();
    }

    // Antes de enviar el formulario, necesitamos habilitar los selects
    // para que sus valores sean enviados en el POST (campos disabled no se envían)
    const form = document.getElementById('socioeconomicForm');
    if (form) {
        form.addEventListener('submit', function () {
            dependentSelects.forEach(select => {
                if (select) {
                    // Habilitar antes del submit
                    select.disabled = false;
                }
            });
        });
    }
});
