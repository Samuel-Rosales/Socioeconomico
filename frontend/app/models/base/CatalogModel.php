<?php

namespace App\Models\Base;

use App\Services\ApiService;

/**
 * Clase base para todos los modelos de catálogos
 * Consume la API del backend para operaciones CRUD
 */
abstract class CatalogModel
{
    protected $id;
    protected $nombre;
    protected $activo;
    protected $resourceName; // Debe ser definido en cada clase hija (ej: 'nacionalidades')
    protected $apiService;

    public function __construct($data = [])
    {
        $this->apiService = new ApiService();

        if (!empty($data)) {
            $this->fill($data);
        }
    }

    /**
     * Llenar el modelo con datos
     */
    protected function fill($data)
    {
        if (isset($data['id'])) $this->id = $data['id'];
        if (isset($data['nombre'])) $this->nombre = $data['nombre'];
        if (isset($data['activo'])) $this->activo = $data['activo'];
    }

    /**
     * Obtener todos los registros desde la API
     */
    public function getAll($includeInactive = false)
    {
        try {
            $endpoint = "/catalogo/{$this->resourceName}";
            $response = $this->apiService->get($endpoint);

            if ($response['success'] && isset($response['data'])) {
                $data = $this->extractApiData($response['data']);

                if (!is_array($data)) {
                    return [];
                }

                // Filtrar inactivos si es necesario
                if (!$includeInactive) {
                    $data = array_filter($data, function ($item) {
                        return isset($item['activo']) && $item['activo'] == 1;
                    });
                }

                return array_values($data);
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener registro por ID desde la API
     */
    public function getById($id)
    {
        try {
            $endpoint = "/catalogo/{$this->resourceName}/{$id}";
            $response = $this->apiService->get($endpoint);

            if ($response['success'] && isset($response['data'])) {
                return $this->extractApiData($response['data']);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Crear nuevo registro vía API
     */
    public function create($data)
    {
        if (!$this->validate($data)) {
            return false;
        }

        try {
            $endpoint = "/catalogo/{$this->resourceName}";
            $response = $this->apiService->post($endpoint, $data);

            $payload = isset($response['data']) ? $this->extractApiData($response['data']) : null;

            if ($response['success'] && is_array($payload) && isset($payload['id'])) {
                return $payload['id'];
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Actualizar registro vía API
     */
    public function update($id, $data)
    {
        if (!$this->validate($data)) {
            return false;
        }

        try {
            $endpoint = "/catalogo/{$this->resourceName}/{$id}";
            $response = $this->apiService->put($endpoint, $data);

            return $response['success'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Eliminar registro (soft delete) vía API
     */
    public function delete($id)
    {
        try {
            $endpoint = "/catalogo/{$this->resourceName}/{$id}";
            $response = $this->apiService->delete($endpoint);

            return $response['success'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Activar/Desactivar registro vía API
     */
    public function toggleActive($id)
    {
        try {
            $endpoint = "/catalogo/{$this->resourceName}/{$id}/toggle";
            $response = $this->apiService->patch($endpoint, []);

            return $response['success'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validar datos (validación básica del lado del cliente)
     */
    protected function validate($data)
    {
        if (empty($data['nombre'])) {
            return false;
        }

        return true;
    }

    /**
     * Contar registros
     */
    public function count($includeInactive = false)
    {
        $all = $this->getAll($includeInactive);
        return count($all);
    }

    protected function extractApiData($payload)
    {
        if (!is_array($payload)) {
            return null;
        }

        // Formato estándar: {success, data, message}
        if (array_key_exists('success', $payload) && array_key_exists('data', $payload)) {
            return $payload['data'];
        }

        // Formato legacy: array plano
        return $payload;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function isActivo()
    {
        return $this->activo == 1;
    }

    public function getResourceName()
    {
        return $this->resourceName;
    }

    // Setters
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function setActivo($activo)
    {
        $this->activo = $activo;
    }
}
