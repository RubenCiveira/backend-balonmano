<?php

namespace Civi\Balonmano\Features\Cancha;

class Cancha
{
    public static function from(array $data): Cancha
    {
        return new Cancha(
            $data['code'],
            $data['label'],
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
    ) {
    }
}
