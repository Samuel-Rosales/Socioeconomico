<?php

namespace App\Services;

class CatalogoService
{
    private $api;

    public function __construct(ApiService $api = null)
    {
        $this->api = $api ?: new ApiService();
    }

    public function roles()
    {
        return $this->api->get('/catalogo/rol');
    }

    public function institutos()
    {
        return $this->api->get('/catalogo/instituto');
    }

    public function catalogos()
    {
        return $this->api->get('/catalogo');
    }

    public function catalogo($resource, $params = [])
    {
        $resource = trim((string)$resource);
        return $this->api->get('/catalogo/' . rawurlencode($resource), $params);
    }

    public function catalogoAdmin($resource, $params = [])
    {
        $resource = trim((string)$resource);
        return $this->api->get('/catalogo-admin/' . rawurlencode($resource), $params);
    }

    public function adminCreate($resource, $data = [])
    {
        $resource = trim((string)$resource);
        return $this->api->post('/catalogo-admin/' . rawurlencode($resource), $data);
    }

    public function adminUpdate($resource, $id, $data = [])
    {
        $resource = trim((string)$resource);
        return $this->api->put('/catalogo-admin/' . rawurlencode($resource) . '/' . (int)$id, $data);
    }

    public function adminDelete($resource, $id, $params = [])
    {
        $resource = trim((string)$resource);
        return $this->api->delete('/catalogo-admin/' . rawurlencode($resource) . '/' . (int)$id . (!empty($params) ? ('?' . http_build_query($params)) : ''));
    }

    public function adminRestore($resource, $id, $data = [])
    {
        $resource = trim((string)$resource);
        return $this->api->post('/catalogo-admin/' . rawurlencode($resource) . '/' . (int)$id . '/restore', $data);
    }

    public function carreraActivos()
    {
        return $this->api->get('/catalogo-admin/carrera/activos');
    }
}
