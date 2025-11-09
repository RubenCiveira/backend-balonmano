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
    /**
     * @return Temporada[]
     */
    public function temporadas(Territorial $territorial, ?Provincial $provincial = null): array {
        $key = "temporadas_" . $territorial->code;
        if(  $this->cache->has($key) ) {
            $all = json_decode($this->cache->get($key), true);
            $result = [];
            foreach($all as $v) {
                $result[] = Temporada::from($v);
            }
            return $result;
        }
        $temporadas = $this->extractor->extractTemporadas($territorial, $provincial);;
        $this->cache->set($key, json_encode($temporadas), DateInterval::createFromDateString("1 hour"));
        return $temporadas;
    }

    /**
     * @return Categoria[]
     */
    public function categorias(Temporada $temporada): array {
        return $this->categorias->categorias( $temporada );
    }
}