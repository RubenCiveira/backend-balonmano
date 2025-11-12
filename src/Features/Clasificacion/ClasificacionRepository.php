<?php

namespace Civi\Balonmano\Features\Clasificacion;

use Civi\Balonmano\Features\Fase\Fase;
use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Civi\Balonmano\Features\Jornada\Jornada;
use Civi\Balonmano\Shared\Scrap\Extractor;

class ClasificacionRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor
    ) {
    }

    /**
     * @return Clasificacion[]
     */
    public function clasificacion(Jornada|Fase $jornada): array
    {
        $key = "clasificaciones_" . $jornada->uid();
        if (false && $this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach ($all as $v) {
                $result[] = Clasificacion::from($v);
            }
            return $result;
        }
        $categorias = $this->extractor->extractClasificacion($jornada);
        $this->cache->set($key, json_encode($categorias), DateInterval::createFromDateString("4 hour"));
        return $categorias;
    }
}
