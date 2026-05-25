<?php
namespace App\Services;

use App\Core\Validator;
use App\Models\EncuestaModel;
use Exception;

class EncuestaService {
    private $validator;
    private $model;

    public function __construct() {
        $this->validator = new Validator();
        $this->model = new EncuestaModel();
    }

    public function checkDuplicados($cedula = null, $email = null)
    {
        return $this->model->checkDuplicados($cedula, $email);
    }

    public function listarResumen($institutoId = null, array $options = [])
    {
        try {
            $wantsPaginationOrFilters = array_key_exists('page', $options)
                || array_key_exists('per_page', $options)
                || array_key_exists('q', $options)
                || array_key_exists('carrera_id', $options)
                || array_key_exists('estrato', $options);

            if ($wantsPaginationOrFilters) {
                $page = isset($options['page']) && is_numeric($options['page']) ? (int)$options['page'] : 1;
                $perPage = isset($options['per_page']) && is_numeric($options['per_page']) ? (int)$options['per_page'] : 10;

                if ($page < 1) {
                    $page = 1;
                }
                if ($perPage < 1) {
                    $perPage = 10;
                }
                if ($perPage > 100) {
                    $perPage = 100;
                }

                $queryOptions = [
                    'q' => isset($options['q']) ? (string)$options['q'] : null,
                    'carrera_id' => $options['carrera_id'] ?? null,
                    'estrato' => $options['estrato'] ?? null,
                    'page' => $page,
                    'per_page' => $perPage,
                ];

                $result = $this->model->getResumenPaginated($institutoId, $queryOptions);
                $items = $this->formatResumenItems($result['items']);

                return [
                    'success' => true,
                    'data' => [
                        'items' => $items,
                        'pagination' => $result['pagination'],
                    ],
                ];
            }

            // Compatibilidad: si no se pidió paginación/filtros, devolvemos el listado completo como antes.
            $items = !empty($institutoId)
                ? $this->model->getAllByInstituto((int)$institutoId)
                : $this->model->getAll();
            $items = $this->formatResumenItems($items);

            return [
                'success' => true,
                'data' => [
                    'items' => $items,
                ],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Error al listar encuestas',
                'errors' => ['database' => [$e->getMessage()]],
            ];
        }
    }

    public function obtenerDetalle($id, $institutoId = null)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'ID inválido',
                'errors' => ['id' => ['El id debe ser numérico.']],
            ];
        }

        $id = (int)$id;

        try {
            $detalle = $this->model->obtenerDetalleCompleto($id, $institutoId);

            if (!$detalle) {
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Encuesta no encontrada',
                    'errors' => ['encuesta' => ['No existe o está inactiva (o no pertenece a tu instituto).']],
                ];
            }

            return [
                'success' => true,
                'data' => $detalle,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Error al obtener encuesta',
                'errors' => ['database' => [$e->getMessage()]],
            ];
        }
    }

    public function registrarEncuesta(array $requestData) {
        $requestData = $this->normalizarRequestData($requestData);

        // 1. Definimos las reglas para los 50 campos (resumen)
        $rules = [
            // Fecha/hora de inicio del llenado (métrica de tiempo de respuesta)
            'inicio'             => '',
            'email'              => 'required|email',
            'nombres'            => 'required',
            'apellidos'          => 'required',
            'cedula'             => 'required',
            'nacionalidad_id'    => 'required|numeric',
            'telefono'           => 'required',
            // En BD es DATE; el frontend envía YYYY-MM-DD
            'fecha_nacimiento'   => 'required',
            'sexo_id'            => 'required|numeric',
            'estado_civil_id'    => 'required|numeric',
            'tipo_estudiante_id' => 'required|numeric',
            'carrera_id'         => 'required|numeric',
            'semestre_id'        => 'required|numeric',
            'estudio_fya'        => 'required|numeric', // Booleano 0 o 1
            'direccion'          => 'required',
            // Multi-tenant
            'instituto_id'        => 'required|numeric',
        ];

        $rules = array_merge($rules, [
            'hijos'              => 'required|numeric',
            'numero_hijos'       => 'numeric', // Opcional si hijos es 0
            'discapacidad'       => '',        // Opcional, solo texto
            'enfermedad_cronica' => '',        // Opcional

            // En el frontend actual estas secciones no están marcadas como requeridas
            'tipo_convivencia_id'=> 'numeric',
            'numero_habitantes'  => 'numeric',
        ]);

        $rules = array_merge($rules, [
            'tipo_vivienda_id'                   => 'numeric',
            'tenencia_vivienda_id'               => 'numeric',
            'frecuencia_servicio_agua_id'        => 'numeric',
            'frecuencia_servicio_aseo_id'        => 'numeric',
            'frecuencia_servicio_electricidad_id'=> 'numeric',
            'frecuencia_servicio_gas_id'         => 'numeric',
            'transporte_id'                      => 'required|numeric',
        ]);

        $rules = array_merge($rules, [
            'condicion_laboral_id'       => 'numeric',
            'trabajo_relacion_id'        => 'numeric',
            'tipo_organizacion_id'       => 'numeric',
            'sector_trabajo_id'          => 'numeric',
            'categoria_ocupacional_id'   => 'numeric',
            'dependencia_economica_id'   => 'required|numeric',
            'fuente_ingreso_familiar_id' => 'required|numeric',
            'ingreso_familiar_id'        => 'required|numeric',
            'numero_ocupantes_familia'   => 'numeric',
        ]);

        $rules = array_merge($rules, [
            'nivel_eduacion_padre_id'        => 'required|numeric',
            'trabaja_padre'                  => 'required|numeric',
            'tipo_empresa_padre_id'          => 'required|numeric',
            'categoria_ocupacional_padre_id' => 'required|numeric',
            'sector_trabajo_padre_id'        => 'required|numeric',
            'padre_en_venezuela'             => 'required|numeric',
            'padre_egresado_iujo'            => 'required|numeric',
            
            'nivel_eduacion_madre_id'        => 'required|numeric',
            'trabaja_madre'                  => 'required|numeric',
            'tipo_empresa_madre_id'          => 'required|numeric',
            'categoria_ocupacional_madre_id' => 'required|numeric',
            'sector_trabajo_madre_id'        => 'required|numeric',
            'madre_en_venezuela'             => 'required|numeric',
            'madre_egresada_iujo'            => 'required|numeric',

            'veracidad_id'                   => 'required|numeric',
            // En el formulario es opcional (puede ser "Ninguna")
            'tipo_beca_id'                   => 'numeric',
            // En frontend se envía un archivo (cedula_file), no un URL.
            // Dejamos url_cedula como opcional para no romper el flujo.
            'url_cedula'                     => '',
        ]);

        // 2. Validamos
        $errores = $this->validator->validate($requestData, $rules);

        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        // 3. Si todo está bien, preparamos los datos para el modelo
        try {
            // Separamos los datos de la tabla principal de las relaciones (activos, servicios)
            $datosPrincipales = $this->limpiarDatos($requestData, $rules);
            $relaciones = $this->normalizarRelaciones($requestData);

            $id = $this->model->guardarCompleta($datosPrincipales, $relaciones);
            return ['success' => true, 'id' => $id];

        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['database' => $e->getMessage()]];
        }
    }

    public function actualizarEncuesta($id, array $requestData)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'ID inválido',
                'errors' => ['id' => ['El id debe ser numérico.']],
            ];
        }

        $id = (int)$id;
        $requestData = $this->normalizarRequestData($requestData);

        $disallowed = ['id', 'inicio', 'creado', 'activo'];
        foreach ($disallowed as $key) {
            if (array_key_exists($key, $requestData)) {
                unset($requestData[$key]);
            }
        }

        $allowedFields = [
            'email', 'nombres', 'apellidos', 'cedula', 'telefono', 'fecha_nacimiento',
            'direccion', 'hijos', 'numero_hijos', 'discapacidad', 'enfermedad_cronica',
            'estudio_fya', 'numero_habitantes', 'numero_ocupantes_familia', 'url_cedula',
            'instituto_id', 'nacionalidad_id', 'sexo_id', 'tipo_estudiante_id', 'carrera_id',
            'semestre_id', 'estado_civil_id', 'condicion_laboral_id', 'trabajo_relacion_id',
            'tipo_organizacion_id', 'sector_trabajo_id', 'categoria_ocupacional_id',
            'tipo_convivencia_id', 'tipo_vivienda_id', 'tenencia_vivienda_id',
            'frecuencia_servicio_agua_id', 'frecuencia_servicio_aseo_id',
            'frecuencia_servicio_electricidad_id', 'frecuencia_servicio_gas_id',
            'transporte_id', 'dependencia_economica_id', 'fuente_ingreso_familiar_id',
            'ingreso_familiar_id', 'nivel_eduacion_padre_id', 'trabaja_padre',
            'tipo_empresa_padre_id', 'categoria_ocupacional_padre_id', 'sector_trabajo_padre_id',
            'padre_en_venezuela', 'padre_egresado_iujo', 'nivel_eduacion_madre_id',
            'trabaja_madre', 'tipo_empresa_madre_id', 'categoria_ocupacional_madre_id',
            'sector_trabajo_madre_id', 'madre_en_venezuela', 'madre_egresada_iujo',
            'veracidad_id', 'tipo_beca_id',
        ];

        $numericFields = [
            'hijos', 'numero_hijos', 'estudio_fya', 'numero_habitantes', 'numero_ocupantes_familia',
            'instituto_id', 'nacionalidad_id', 'sexo_id', 'tipo_estudiante_id', 'carrera_id',
            'semestre_id', 'estado_civil_id', 'condicion_laboral_id', 'trabajo_relacion_id',
            'tipo_organizacion_id', 'sector_trabajo_id', 'categoria_ocupacional_id',
            'tipo_convivencia_id', 'tipo_vivienda_id', 'tenencia_vivienda_id',
            'frecuencia_servicio_agua_id', 'frecuencia_servicio_aseo_id',
            'frecuencia_servicio_electricidad_id', 'frecuencia_servicio_gas_id',
            'transporte_id', 'dependencia_economica_id', 'fuente_ingreso_familiar_id',
            'ingreso_familiar_id', 'nivel_eduacion_padre_id', 'trabaja_padre',
            'tipo_empresa_padre_id', 'categoria_ocupacional_padre_id', 'sector_trabajo_padre_id',
            'padre_en_venezuela', 'padre_egresado_iujo', 'nivel_eduacion_madre_id',
            'trabaja_madre', 'tipo_empresa_madre_id', 'categoria_ocupacional_madre_id',
            'sector_trabajo_madre_id', 'madre_en_venezuela', 'madre_egresada_iujo',
            'veracidad_id', 'tipo_beca_id',
        ];

        $payload = [];
        foreach ($allowedFields as $field) {
            if (!array_key_exists($field, $requestData)) {
                continue;
            }

            $value = $requestData[$field];
            if (in_array($field, $numericFields, true)) {
                if ($value === '' || $value === null) {
                    $payload[$field] = null;
                } elseif (is_numeric($value)) {
                    $payload[$field] = (int)$value;
                }
            } else {
                if (is_string($value)) {
                    $payload[$field] = trim($value);
                } elseif ($value === null) {
                    $payload[$field] = null;
                }
            }
        }

        $relaciones = $this->normalizarRelaciones($requestData);
        $incluyeRelaciones = isset($requestData['activos'])
            || isset($requestData['servicios'])
            || isset($requestData['ambientes'])
            || isset($requestData['activos_vivienda'])
            || isset($requestData['servicios_vivienda'])
            || isset($requestData['ambientes_vivienda']);

        if (empty($payload) && !$incluyeRelaciones) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'No hay datos para actualizar',
                'errors' => ['request' => ['Debes enviar al menos un campo editable.']],
            ];
        }

        try {
            $ok = $this->model->actualizarCompleta($id, $payload, $incluyeRelaciones ? $relaciones : []);
            if (!$ok) {
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Encuesta no encontrada',
                    'errors' => ['encuesta' => ['No existe o está inactiva.']],
                ];
            }

            $detalle = $this->model->obtenerDetalleCompleto($id);
            return [
                'success' => true,
                'data' => $detalle,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Error al actualizar la encuesta',
                'errors' => ['database' => [$e->getMessage()]],
            ];
        }
    }

    private function limpiarDatos($data, $rules) {
        // Solo dejamos pasar los campos que definimos en las reglas
        return array_intersect_key($data, $rules);
    }

    private function normalizarRelaciones(array $requestData)
    {
        // Compatibilidad:
        // - Frontend: activos_vivienda[], servicios_vivienda[], ambientes_vivienda[]
        // - Backend legacy: activos, servicios, ambientes

        $activos = $requestData['activos'] ?? ($requestData['activos_vivienda'] ?? []);
        $servicios = $requestData['servicios'] ?? ($requestData['servicios_vivienda'] ?? []);
        $ambientes = $requestData['ambientes'] ?? ($requestData['ambientes_vivienda'] ?? []);

        return [
            'activos' => is_array($activos) ? $activos : [],
            'servicios' => is_array($servicios) ? $servicios : [],
            'ambientes' => is_array($ambientes) ? $ambientes : [],
        ];
    }

    private function normalizarRequestData(array $requestData)
    {
        // Normalizaciones para compatibilidad frontend/backend
        $aliases = [
            // Laboral
            'relacion_laboral_id' => 'trabajo_relacion_id',

            // Frecuencias (frontend usa nombres cortos)
            'frecuencia_agua_id' => 'frecuencia_servicio_agua_id',
            'frecuencia_aseo_id' => 'frecuencia_servicio_aseo_id',
            'frecuencia_electricidad_id' => 'frecuencia_servicio_electricidad_id',
            'frecuencia_gas_id' => 'frecuencia_servicio_gas_id',

            // Economía
            'fuente_ingreso_id' => 'fuente_ingreso_familiar_id',

            // Padre/Madre (frontend usa *_educacion_* y *_trabaja)
            'nivel_educacion_padre_id' => 'nivel_eduacion_padre_id',
            'padre_trabaja' => 'trabaja_padre',
            'nivel_educacion_madre_id' => 'nivel_eduacion_madre_id',
            'madre_trabaja' => 'trabaja_madre',
        ];

        foreach ($aliases as $from => $to) {
            if (isset($requestData[$from]) && !isset($requestData[$to])) {
                $requestData[$to] = $requestData[$from];
            }
        }

        // Estandarizar nombres antes de persistirlos.
        foreach (['nombres', 'apellidos'] as $campoNombre) {
            if (isset($requestData[$campoNombre])) {
                $requestData[$campoNombre] = $this->normalizarTextoNombre($requestData[$campoNombre]);
            }
        }

        if (isset($requestData['inicio'])) {
            $inicioNormalizado = $this->normalizarFechaHora($requestData['inicio']);
            if ($inicioNormalizado !== null) {
                $requestData['inicio'] = $inicioNormalizado;
            } else {
                unset($requestData['inicio']);
            }
        }

        // Convertir strings vacíos a NULL para claves *_id (evita violaciones de FK)
        foreach ($requestData as $key => $value) {
            if (substr($key, -3) === '_id' && is_string($value) && trim($value) === '') {
                $requestData[$key] = null;
            }
        }

        return $requestData;
    }

    private function normalizarFechaHora($value)
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $candidate = str_replace('T', ' ', $value);

        $formats = ['Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $candidate);
            if ($dt instanceof \DateTime) {
                return $dt->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime($candidate);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function normalizarTextoNombre($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $value;
        }

        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim((string)$value);

        if (function_exists('mb_convert_case')) {
            return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
        }

        return ucwords(strtolower($value));
    }

    private function formatResumenItems($items)
    {
        if (!is_array($items)) {
            return [];
        }

        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($item['creado']) && is_string($item['creado']) && trim($item['creado']) !== '') {
                $items[$idx]['creado_raw'] = $item['creado'];
                $items[$idx]['creado'] = $this->formatFechaBonita($item['creado']);
            }
        }

        return $items;
    }

    private function formatFechaBonita($value)
    {
        if (!is_string($value) || trim($value) === '') {
            return $value;
        }

        try {
            $dt = new \DateTime($value);
            $meses = [
                1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun',
                7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic',
            ];

            $mes = isset($meses[(int)$dt->format('n')]) ? $meses[(int)$dt->format('n')] : $dt->format('m');
            return $dt->format('d') . ' ' . $mes . ' ' . $dt->format('Y, h:i A');
        } catch (Exception $e) {
            return $value;
        }
    }
}