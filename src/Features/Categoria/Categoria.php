<?php

namespace Civi\Balonmano\Features\Categoria;

use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;

class Categoria
{
    public static function from(array $data): Categoria
    {
        return new Categoria(
            $data['code'],
            $data['label'],
            Temporada::from($data['temporada'])
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Temporada $temporada
    ) {
    }

    public function territorial(): Territorial
    {
        return $this->temporada->territorial();
    }

    public function uid()
    {
        return $this->temporada->uid() . "_" . $this->code;
    }
}
