<?php

namespace Civi\Balonmano\Features\Categoria;

use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Competicion\CompeticionRepository;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Shared\Scrap\Extractor;
use DateInterval;
use Psr\SimpleCache\CacheInterface;

class CategoriaRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor,
        private readonly CompeticionRepository $categorias
    ) {
    }

    public function clearCache(Temporada $temporada): void
    {
        $key = $this->cacheKey($temporada);
        $this->cache->delete($key);
    }
    /**
     * @return Categoria[]
     */
    public function categorias(Temporada $temporada): array
    {
        $key = $this->cacheKey($temporada);
        if ($this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach ($all as $v) {
                $result[] = Categoria::from($v);
            }
            return $result;
        }
        $categorias = $this->extractor->extractCategorias($temporada);
        $this->cache->set($key, json_encode($categorias), DateInterval::createFromDateString("1 week"));
        return $categorias;
    }

    private function cacheKey(Temporada $temporada): string
    {
        return "categorias_" . $temporada->uid();
    }
}
