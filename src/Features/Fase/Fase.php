<?php

namespace Civi\Balonmano\Features\Fase;

use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;

class Fase
{
    public static function from(array $data): Fase
    {
        return new Fase(
            $data['code'],
            $data['label'],
            Competicion::from($data['competicion'])
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Competicion $competicion
    ) {
    }

    public function temporada(): Temporada
    {
        return $this->competicion->temporada();
    }

    public function territorial(): Territorial
    {
        return $this->competicion->territorial();
    }

    public function uid()
    {
        return $this->competicion->uid() . "_" . $this->code;
    }
}
