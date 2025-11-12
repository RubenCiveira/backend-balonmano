<?php

namespace Civi\Balonmano\Features\Equipo;

class Inscrito
{
    public static function from(array $data): Inscrito
    {
        return new Inscrito(
            $data['nombre'],
            $data['goles'],
            $data['edad'],
            $data['foto'],
            $data['baja'],
        );
    }
    public function __construct(
        public readonly string $nombre,
        public readonly int $goles,
        public readonly int $edad,
        public readonly string $foto,
        public readonly string $baja
    ) {
    }
}
