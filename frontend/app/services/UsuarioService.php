<?php

namespace App\Services;

class UsuarioService
{
    private $api;

    public function __construct(ApiService $api = null)
    {
        $this->api = $api ?: new ApiService();
    }

    public function listar()
    {
        return $this->api->get('/usuario');
    }

    public function crear(array $data)
    {
        return $this->api->post('/usuario', $data);
    }

    public function actualizar($id, array $data)
    {
        $id = is_numeric($id) ? (int)$id : 0;
        return $this->api->put('/usuario/' . $id, $data);
    }

    public function eliminar($id)
    {
        $id = is_numeric($id) ? (int)$id : 0;
        return $this->api->delete('/usuario/' . $id);
    }
}
