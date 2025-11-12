<?php

namespace Civi\Balonmano\Features\Equipo;

use Psr\SimpleCache\CacheInterface;
use Civi\Balonmano\Shared\Scrap\Extractor;
use DateInterval;

class EquipoRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Extractor $extractor
    ) {
    }

    public function clearCache(Equipo $equipo): void
    {
        $key = $this->cacheKey($equipo);
        $this->cache->delete($key);
    }
    /**
     */
    public function detalles(Equipo $equipo): DetalleEquipo
    {
        $key = $this->cacheKey($equipo);
        if (false && $this->cache->has($key)) {
            $all = json_decode($this->cache->get($key), true);
            return DetalleEquipo::from($all);
        }
        $categorias = $this->extractor->extractDetallesEquipo($equipo);
        $this->cache->set($key, json_encode($categorias), DateInterval::createFromDateString("1 week"));
        return $categorias;
    }

    public function cacheKey(Equipo $equipo): string
    {
        return "detalle_equipo_" . $equipo->uid();
    }

}
