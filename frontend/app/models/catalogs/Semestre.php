<?php

namespace App\Models\Catalogs;

use App\Models\Base\CatalogModel;

/**
 * Modelo Semestre con campo adicional 'numero'
 */
class Semestre extends CatalogModel
{
    protected $resourceName = 'semestre';
    protected $numero;

    protected function fill($data)
    {
        parent::fill($data);
        if (isset($data['numero'])) $this->numero = $data['numero'];
    }

    public function getNumero()
    {
        return $this->numero;
    }

    public function setNumero($numero)
    {
        $this->numero = $numero;
    }
}
