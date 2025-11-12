<?php

namespace Civi\Balonmano\Features\Equipo;

use Civi\Balonmano\Features\Fase\Fase;

class Equipo
{
    public static function from(array $data): Equipo
    {
        return new Equipo(
            $data['code'],
            $data['label'],
            $data['logo']
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly string $logo
    ) {
    }

    public function uid()
    {
        return $this->code;
    }
}
