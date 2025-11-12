<?php

namespace Civi\Balonmano\Features\Competicion;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Fase\FaseRepository;
use Civi\Balonmano\Shared\Scrap\Extractor;
use DateInterval;
use Psr\SimpleCache\CacheInterface;

class CompeticionRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor,
        private readonly FaseRepository $categorias
    ) {
    }

    public function clearCache(Categoria $categoria): void
    {
        $key = $this->cacheKey($categoria);
        $this->cache->delete($key);
    }

    /**
     * @return Competicion[]
     */
    public function competiciones(Categoria $categoria): array
    {
        $key = $this->cacheKey($categoria);
        if ($this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach ($all as $v) {
                $result[] = Competicion::from($v);
            }
            return $result;
        }
        $competiciones = $this->extractor->extractCompeticion($categoria);
        $this->cache->set($key, json_encode($competiciones), DateInterval::createFromDateString("1 week"));
        return $competiciones;
    }

    public function cacheKey(Categoria $categoria): string
    {
        return  "competiciones_" . $categoria->uid();
    }
}
