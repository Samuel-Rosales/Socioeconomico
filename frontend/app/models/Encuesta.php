<?php

namespace App\Models;

/**
 * Encuesta - Modelo para el formulario socioeconómico IUJO
 */
class Encuesta
{
    private $data = [];
    private $errors = [];

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Valida todos los campos del formulario
     */
    public function validate()
    {
        $this->errors = [];

        // Datos Personales - Requeridos
        $this->validateRequired('email', 'El email es requerido');
        $this->validateEmail('email', 'El email no es válido');
        $this->validateRequired('nombres', 'El nombre es requerido');
        $this->validateRequired('apellidos', 'Los apellidos son requeridos');
        $this->validateRequired('cedula', 'La cédula es requerida');
        $this->validateRequired('telefono', 'El teléfono es requerido');
        $this->validateRequired('fecha_nacimiento', 'La fecha de nacimiento es requerida');
        $this->validateRequired('direccion', 'La dirección es requerida');

        // Foreign Keys - Requeridos
        $this->validateRequired('nacionalidad_id', 'La nacionalidad es requerida');
        $this->validateRequired('sexo_id', 'El sexo es requerido');
        $this->validateRequired('tipo_estudiante_id', 'El tipo de estudiante es requerido');
        $this->validateRequired('carrera_id', 'La carrera es requerida');
        $this->validateRequired('semestre_id', 'El semestre es requerido');
        $this->validateRequired('estado_civil_id', 'El estado civil es requerido');
        $this->validateRequired('transporte_id', 'El transporte es requerido');
        $this->validateRequired('dependencia_economica_id', 'La dependencia económica es requerida');
        $this->validateRequired('fuente_ingreso_id', 'La fuente de ingreso familiar es requerida');
        $this->validateRequired('ingreso_familiar_id', 'El ingreso familiar es requerido');
        $this->validateRequired('veracidad_id', 'La veracidad de la información es requerida');
        $this->validateRequired('nivel_educacion_padre_id', 'El nivel de educación del padre es requerido');
        $this->validateRequired('padre_trabaja', 'Debe indicar si el padre trabaja');
        $this->validateRequired('tipo_empresa_padre_id', 'El tipo de empresa del padre es requerido');
        $this->validateRequired('categoria_ocupacional_padre_id', 'La categoría ocupacional del padre es requerida');
        $this->validateRequired('sector_trabajo_padre_id', 'El sector de trabajo del padre es requerido');
        $this->validateRequired('padre_en_venezuela', 'Debe indicar si el padre está en Venezuela');
        $this->validateRequired('padre_egresado_iujo', 'Debe indicar si el padre es egresado del IUJO');
        $this->validateRequired('nivel_educacion_madre_id', 'El nivel de educación de la madre es requerido');
        $this->validateRequired('madre_trabaja', 'Debe indicar si la madre trabaja');
        $this->validateRequired('tipo_empresa_madre_id', 'El tipo de empresa de la madre es requerido');
        $this->validateRequired('categoria_ocupacional_madre_id', 'La categoría ocupacional de la madre es requerida');
        $this->validateRequired('sector_trabajo_madre_id', 'El sector de trabajo de la madre es requerido');
        $this->validateRequired('madre_en_venezuela', 'Debe indicar si la madre está en Venezuela');
        $this->validateRequired('madre_egresada_iujo', 'Debe indicar si la madre es egresada del IUJO');

        // Validaciones condicionales
        if ($this->get('hijos') == 1) {
            $this->validateRequired('numero_hijos', 'El número de hijos es requerido');
        }

        return empty($this->errors);
    }

    private function validateRequired($field, $message)
    {
        $value = $this->get($field);

        // Importante: en PHP empty('0') es true, pero para radios Sí/No el 0 es válido.
        if ($value === null) {
            $this->errors[$field] = $message;
            return;
        }

        if (is_string($value) && trim($value) === '') {
            $this->errors[$field] = $message;
            return;
        }

        if (is_array($value) && empty($value)) {
            $this->errors[$field] = $message;
        }
    }

    private function validateEmail($field, $message)
    {
        $value = $this->get($field);
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function all()
    {
        return $this->data;
    }

    /**
     * Convierte los datos a array para enviar a la API
     */
    public function toArray()
    {
        return [
            // Métrica de inicio del llenado
            'inicio' => $this->get('inicio'),

            // Datos Personales
            'email' => $this->get('email'),
            'nombres' => $this->get('nombres'),
            'apellidos' => $this->get('apellidos'),
            'cedula' => $this->get('cedula'),
            'telefono' => $this->get('telefono'),
            'fecha_nacimiento' => $this->get('fecha_nacimiento'),
            'direccion' => $this->get('direccion'),
            'hijos' => $this->get('hijos', 0),
            'numero_hijos' => $this->get('numero_hijos', 0),
            'discapacidad' => $this->get('discapacidad'),
            'enfermedad_cronica' => $this->get('enfermedad_cronica'),
            'estudio_fya' => $this->get('estudio_fya', 0),
            'url_cedula' => $this->get('url_cedula'),

            // Datos Académicos
            'nacionalidad_id' => $this->get('nacionalidad_id'),
            'sexo_id' => $this->get('sexo_id'),
            'tipo_estudiante_id' => $this->get('tipo_estudiante_id'),
            'carrera_id' => $this->get('carrera_id'),
            'semestre_id' => $this->get('semestre_id'),
            'estado_civil_id' => $this->get('estado_civil_id'),

            // Datos Laborales
            'condicion_laboral_id' => $this->get('condicion_laboral_id'),
            // En el formulario se llama relacion_laboral_id
            'trabajo_relacion_id' => $this->get('relacion_laboral_id', $this->get('trabajo_relacion_id')),
            'tipo_organizacion_id' => $this->get('tipo_organizacion_id'),
            'sector_trabajo_id' => $this->get('sector_trabajo_id'),
            'categoria_ocupacional_id' => $this->get('categoria_ocupacional_id'),

            // Datos de Vivienda
            'tipo_convivencia_id' => $this->get('tipo_convivencia_id'),
            'tipo_vivienda_id' => $this->get('tipo_vivienda_id'),
            'tenencia_vivienda_id' => $this->get('tenencia_vivienda_id'),
            'numero_habitantes' => $this->get('numero_habitantes', 1),
            'numero_ocupantes_familia' => $this->get('numero_ocupantes_familia', 1),

            // Servicios
            // En el formulario se llaman frecuencia_agua_id, etc.
            'frecuencia_servicio_agua_id' => $this->get('frecuencia_agua_id', $this->get('frecuencia_servicio_agua_id')),
            'frecuencia_servicio_aseo_id' => $this->get('frecuencia_aseo_id', $this->get('frecuencia_servicio_aseo_id')),
            'frecuencia_servicio_electricidad_id' => $this->get('frecuencia_electricidad_id', $this->get('frecuencia_servicio_electricidad_id')),
            'frecuencia_servicio_gas_id' => $this->get('frecuencia_gas_id', $this->get('frecuencia_servicio_gas_id')),

            // Transporte y Economía
            'transporte_id' => $this->get('transporte_id'),
            'dependencia_economica_id' => $this->get('dependencia_economica_id'),
            // En el formulario se llama fuente_ingreso_id
            'fuente_ingreso_familiar_id' => $this->get('fuente_ingreso_id', $this->get('fuente_ingreso_familiar_id')),
            'ingreso_familiar_id' => $this->get('ingreso_familiar_id'),

            // Datos del Padre
            // En BD/Backend existe el typo nivel_eduacion_padre_id; en formulario es nivel_educacion_padre_id
            'nivel_eduacion_padre_id' => $this->get('nivel_educacion_padre_id', $this->get('nivel_eduacion_padre_id')),
            // En el formulario se llama padre_trabaja
            'trabaja_padre' => $this->get('padre_trabaja', $this->get('trabaja_padre')),
            'tipo_empresa_padre_id' => $this->get('tipo_empresa_padre_id'),
            'categoria_ocupacional_padre_id' => $this->get('categoria_ocupacional_padre_id'),
            'sector_trabajo_padre_id' => $this->get('sector_trabajo_padre_id'),
            'padre_en_venezuela' => $this->get('padre_en_venezuela'),
            'padre_egresado_iujo' => $this->get('padre_egresado_iujo'),

            // Datos de la Madre
            // En BD/Backend existe el typo nivel_eduacion_madre_id; en formulario es nivel_educacion_madre_id
            'nivel_eduacion_madre_id' => $this->get('nivel_educacion_madre_id', $this->get('nivel_eduacion_madre_id')),
            // En el formulario se llama madre_trabaja
            'trabaja_madre' => $this->get('madre_trabaja', $this->get('trabaja_madre')),
            'tipo_empresa_madre_id' => $this->get('tipo_empresa_madre_id'),
            'categoria_ocupacional_madre_id' => $this->get('categoria_ocupacional_madre_id'),
            'sector_trabajo_madre_id' => $this->get('sector_trabajo_madre_id'),
            'madre_en_venezuela' => $this->get('madre_en_venezuela'),
            'madre_egresada_iujo' => $this->get('madre_egresada_iujo'),

            // Otros
            'veracidad_id' => $this->get('veracidad_id'),
            'tipo_beca_id' => $this->get('tipo_beca_id'),

            // Arrays para relaciones muchos a muchos
            'activos_vivienda' => $this->get('activos_vivienda', []),
            'ambientes_vivienda' => $this->get('ambientes_vivienda', []),
            'servicios_vivienda' => $this->get('servicios_vivienda', []),
        ];
    }
}
