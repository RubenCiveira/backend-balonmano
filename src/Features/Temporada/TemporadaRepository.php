<?php

namespace Civi\Balonmano\Features\Temporada;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Categoria\CategoriaRepository;
use Civi\Balonmano\Features\Provincial\Provincial;
use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Shared\Scrap\Extractor;
use DateInterval;
use Psr\SimpleCache\CacheInterface;

class TemporadaRepository {
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor,
        private readonly CategoriaRepository $categorias
    ) {
    }

    public function clearCache(Territorial $territorial, ?Provincial $provincial = null): void {
        $key = $this->cacheKey($territorial, $provincial);
        $this->cache->delete( $key );
    }

    /**
     * @return Temporada[]
     */
    public function temporadas(Territorial $territorial, ?Provincial $provincial = null): array {
        $key = $this->cacheKey($territorial, $provincial);
        if(  $this->cache->has($key) ) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach($all as $v) {
                $result[] = Temporada::from($v);
            }
            return $result;
        }
        $temporadas = $this->extractor->extractTemporadas($territorial, $provincial);;
        $this->cache->set($key, json_encode($temporadas), DateInterval::createFromDateString("1 week"));
        return $temporadas;
    }

    /**
     * @return Categoria[]
     */
    public function categorias(Temporada $temporada): array {
        return $this->categorias->categorias( $temporada );
    }

    private function cacheKey(Territorial $territorial, ?Provincial $provincial = null): string {
        return "temporadas_" . $territorial->code;
    }
}