<?php

namespace Civi\Balonmano\Features\Fase;

use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Shared\Scrap\Extractor;
use DateInterval;
use Psr\SimpleCache\CacheInterface;

class FaseRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor
    ) {

    }
    /**
     * @return Fase[]
     */
    public function fases(Competicion $competicion): array
    {
        $key = "fases_" . $competicion->uid();
        if ($this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach ($all as $v) {
                $result[] = Fase::from($v);
            }
            return $result;
        }
        $categorias = $this->extractor->extractFase($competicion);
        $this->cache->set($key, json_encode($categorias), DateInterval::createFromDateString("1 week"));
        return $categorias;
    }
}
