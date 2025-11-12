<?php

namespace Civi\Balonmano\Features\Partido;

use DateTimeZone;
use DateTimeImmutable;
use Civi\Balonmano\Features\Cancha\Cancha;
use Civi\Balonmano\Features\Equipo\Equipo;
use Civi\Balonmano\Features\Jornada\Jornada;

class Partido
{
    public static function from(array $data): Partido
    {
        return new Partido(
            $data['code'],
            $data['label'],
            Equipo::from($data['local']),
            Equipo::from($data['visitante']),
            $data['estado'],
            $data['puntosLocal'],
            $data['puntosVisitante'],
            self::dateTimeFromPhpJson( $data['fecha'] ),
            Cancha::from($data['lugar'])
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Equipo $local,
        public readonly Equipo $visitante,
        public readonly string $estado,
        public readonly ?int $puntosLocal = null,
        public readonly ?int $puntosVisitante = null,
        public readonly ?DateTimeImmutable $fecha = null,
        public readonly ?Cancha $lugar = null,
    ) {
    }

    public function isEnded()
    {
        return $this->estado == 'Finalizado';
    }

    public function uid()
    {
        // return $this->jornada->uid() . "_" . $this->code;
        return $this->code;
    }

    private static function dateTimeFromPhpJson(array $obj): ?DateTimeImmutable
    {
        if (!is_array($obj) || !isset($obj['date'], $obj['timezone'])) {
            return null;
        }
        $tz = new DateTimeZone($obj['timezone']);
        // El campo "date" viene como "Y-m-d H:i:s.u"
        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $obj['date'], $tz);
    }
}
