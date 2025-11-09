<?php

namespace Civi\Balonmano\Features\Temporada;

use Civi\Balonmano\Features\Provincial\Provincial;
use Civi\Balonmano\Features\Territorial\Territorial;

class Temporada
{
    public static function from(array $data): Temporada
    {
        return new Temporada(
            $data['code'],
            $data['label'],
            Territorial::from($data['territorial']),
            isset($data['provincial']) ? Territorial::from($data['provincial']) : null
        );
    }

    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Territorial $territorial,
        public readonly ?Provincial $provincial = null
    ) {
    }

    public function territorial(): Territorial
    {
        return $this->territorial;
    }

    public function uid()
    {
        return $this->territorial->code . "_" . $this->code;
    }

}
