<?php

namespace Civi\Balonmano\Features\Territorial;

use Civi\Balonmano\Shared\Scrap\Extractor;
use DateInterval;
use Psr\SimpleCache\CacheInterface;

class TerritorialRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor,
    ) {

    }
    /**
     * @return Territorial[]
     */
    public function territoriales(): array
    {
        $key = "territoriales";
        if ($this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach ($all as $v) {
                $result[] = Territorial::from($v);
            }
            return $result;
        }
        $temporadas = $this->extractor->extractTerritoriales();
        $this->cache->set($key, json_encode($temporadas), DateInterval::createFromDateString("1 hour"));
        return $temporadas;
    }
}
