<?php

namespace Civi\Balonmano\Features\Equipo;

use Civi\Balonmano\Features\Territorial\Territorial;

class Equipo
{
    public static function from(array $data): Equipo
    {
        return new Equipo(
            $data['code'],
            $data['label'],
            Territorial::from($data['territorial']),
            $data['logo']
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Territorial $territorial,
        public readonly string $logo
    ) {
    }
}
