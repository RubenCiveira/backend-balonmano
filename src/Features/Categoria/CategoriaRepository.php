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
    /**
     * @return Categoria[]
     */
    public function categorias(Temporada $temporada): array
    {
        $key = "categorias_" . $temporada->uid();
        if ( $this->cache->has($key)) {
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

    /**
     * @return Competicion[]
     */
    public function competiciones(Categoria $categoria): array
    {
        return [];
    }
}
