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

    public function clearCache(Fase $fase): void
    {
        $key = $this->cacheKey($fase);
        $this->cache->delete($key);
    }
    public function jornadaActual(Fase $fase): ?Jornada
    {
        $key = "jornada_actual_" . $fase->uid();
        if(  $this->cache->has($key) ) {
            $all = json_decode($this->cache->get($key), true);
            return Jornada::from( $all );
        }
        $jornada = $this->extractor->extractJornadaActual($fase);
        $this->cache->set($key, json_encode($jornada), DateInterval::createFromDateString("4 hour"));
        return $jornada;
    }

    /**
     * @return Jornada[]
     */
    public function jornadas(Fase $fase): array
    {
        $key = $this->cacheKey($fase);
        if ($this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach ($all as $v) {
                $result[] = Jornada::from($v);
            }
            return $result;
        }
        $categorias = $this->extractor->extractJornadas($fase);
        $this->cache->set($key, json_encode($categorias), DateInterval::createFromDateString("1 week"));
        return $categorias;
    }

    public function cacheKey(Fase $fase): string
    {
        return "jornadas_" . $fase->uid();
    }
}
