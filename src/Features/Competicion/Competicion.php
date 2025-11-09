<?php

namespace Civi\Balonmano\Features\Competicion;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Territorial\Territorial;

class Competicion
{
    public static function from(array $data): Competicion
    {
        return new Competicion(
            $data['code'],
            $data['label'],
            Categoria::from($data['categoria'])
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Categoria $categoria
    ) {
    }

    public function territorial(): Territorial
    {
        return $this->categoria->territorial();
    }

    public function uid()
    {
        return $this->categoria->uid() . "_" . $this->code;
    }
}
