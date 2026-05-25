<?php

namespace App\Models\Catalogs;

use App\Models\Base\CatalogModel;

/**
 * Modelo Carrera con campo adicional 'codigo'
 */
class Carrera extends CatalogModel
{
    protected $resourceName = 'carrera';
    protected $codigo;

    protected function fill($data)
    {
        parent::fill($data);
        if (isset($data['codigo'])) $this->codigo = $data['codigo'];
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }
}
