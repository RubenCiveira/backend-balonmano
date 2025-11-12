<?php

namespace Civi\Balonmano\Features\Equipo;

class Personal
{
    public static function from(array $data): Personal
    {
        return new Personal(
            $data['nombre'],
            $data['edad'],
            $data['foto'],
            $data['baja']
        );
    }
    public function __construct(
        public readonly string $nombre,
        public readonly int $edad,
        public readonly string $foto,
        public readonly string $baja
    ) {
    }
}
