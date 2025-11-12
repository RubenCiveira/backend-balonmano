<?php

namespace Civi\Balonmano\Features\Equipo;

use Civi\Balonmano\Features\Fase\Fase;

class DetalleEquipo
{
    /*


    */
    public static function from(array $data): DetalleEquipo
    {
        $personal = array_map(function($row) {
            return Personal::from($row);
        }, $data['personal']);
        $jugadores = array_map(function($row) {
            return Inscrito::from($row);
        }, $data['jugadores']);
        $invitados = array_map(function($row) {
            return Invitado::from($row);
        }, $data['invitados']);
        return new DetalleEquipo(
            $data['code'],
            $data['label'],
            Fase::from($data['fase']),
            $data['logo'],
            $data['telefono'],
            $data['email'],
            $data['responsable'],
            $data['cancha'],
            $data['club'],
            $personal,
            $jugadores,
            $invitados
        );
    }
    public function __construct(
        public readonly string $code,
        public readonly string $label,
        public readonly Fase $fase,
        public readonly string $logo,
        public readonly ?string $telefono = null,
        public readonly ?string $email = null,
        public readonly ?string $responsable = null,
        public readonly ?string $cancha = null,
        public readonly ?string $club  = null,
        /** @var Personal */
        public readonly array $personal = [],
        /** @var Inscrito */
        public readonly array $jugadores = [],
        /** @var Invitado */
        public readonly array $invitados = [],
    ) {
    }
}