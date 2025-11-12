<?php

namespace Civi\Balonmano\Features\Partido;

use Civi\Balonmano\Features\Fase\Fase;
use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Civi\Balonmano\Features\Jornada\Jornada;
use Civi\Balonmano\Shared\Scrap\Extractor;

class PartidoRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor
    ) {

    }
    /**
     * @return Partido[]
     */
    public function partidos(Jornada|Fase $jornada): array
    {
        $key = "partidos_" . $jornada->uid();
        if (false && $this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach ($all as $v) {
                $result[] = Partido::from($v);
            }
            return $result;
        }
        $categorias = $this->extractor->extractPartidos($jornada);
        $this->cache->set($key, json_encode($categorias), DateInterval::createFromDateString("1 hour"));
        return $categorias;
    }
}
