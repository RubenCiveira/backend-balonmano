<?php

namespace Civi\Balonmano\Features\Jornada;

use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Shared\Scrap\Extractor;
use DateInterval;
use Psr\SimpleCache\CacheInterface;

class JornadaRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor
    ) {

    }
    /**
     * @return Jornada[]
     */
    public function jornadas(Fase $fase): array
    {
        $key = "jornadas_" . $fase->uid();
        if ($this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach ($all as $v) {
                $result[] = Jornada::from($v);
            }
            return $result;
        }
        $categorias = $this->extractor->extractJornadas($fase);
        $this->cache->set($key, json_encode($categorias), DateInterval::createFromDateString("1 hour"));
        return $categorias;
    }
}
