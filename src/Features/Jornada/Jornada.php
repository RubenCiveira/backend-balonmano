<?php

namespace Civi\Balonmano\Features\Jornada;

use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Temporada\Temporada;
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

    public function competicion(): Competicion
    {
        return $this->fase->competicion;
    }

    public function temporada(): Temporada
    {
        return $this->fase->temporada();
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
