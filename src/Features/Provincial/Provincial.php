<?php

namespace Civi\Balonmano\Features\Provincial;

use Civi\Balonmano\Features\Territorial\Territorial;

class Provincial
{
    public static function from(array $data): Provincial
    {
        return new Provincial(
            $data['code'],
            $data['label'],
            Territorial::from($data['territorial'])
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Territorial $territorial
    ) {
    }

    public function uid() {
        return "provincial_" . $this->code;
    }
}
