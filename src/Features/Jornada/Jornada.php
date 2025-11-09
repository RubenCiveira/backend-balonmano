<?php

namespace Civi\Balonmano\Features\Jornada;

use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Territorial\Territorial;

class Jornada
{
    public static function from(array $data): Jornada
    {
        return new Jornada(
            $data['code'],
            $data['label'],
            Fase::from($data['fase'])
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Fase $fase
    ) {
    }

    public function territorial(): Territorial
    {
        return $this->fase->territorial();
    }

    public function uid()
    {
        return $this->fase->uid() . "_" . $this->code;
    }
}
