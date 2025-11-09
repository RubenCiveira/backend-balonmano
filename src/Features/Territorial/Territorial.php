<?php

namespace Civi\Balonmano\Features\Territorial;

class Territorial
{
    public static function from(array $data): Territorial
    {
        return new Territorial($data['code'], $data['label']);
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label
    ) {
    }

    public function uid() {
        return "territorial_" . $this->code;
    }
}
