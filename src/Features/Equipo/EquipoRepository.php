<?php

namespace Civi\Balonmano\Features\Equipo;

use Psr\SimpleCache\CacheInterface;
use Civi\Balonmano\Shared\Scrap\Extractor;

class EquipoRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor
    ) {

    }
}
