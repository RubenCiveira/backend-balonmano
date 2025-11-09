<?php

namespace Civi\Balonmano\Features\Clasificacion;

use Civi\Balonmano\Features\Equipo\Equipo;
use Civi\Balonmano\Features\Jornada\Jornada;

class Clasificacion
{
    public static function from(array $data): Clasificacion
    {
        return new Clasificacion(
            $data['code'],
            $data['label'],
            Jornada::from($data['jornada']),
            Equipo::from($data['equipo']),
            $data['posicion'],
            $data['puntos'],
            $data['ganados'],
            $data['perdidos'],
            $data['empatados'],
            $data['golesMarcados'],
            $data['golesRecividos'],
            
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Jornada $jornada,
        public readonly Equipo $equipo,
        public readonly int $posicion,
        public readonly int $puntos,
        public readonly int $ganados,
        public readonly int $empatados,
        public readonly int $perdidos,
        public readonly int $golesMarcados,
        public readonly int $golesRecividos,
    ) {
    }

    public function uid()
    {
        return $this->jornada->uid() . "_" . $this->code;
    }
}
