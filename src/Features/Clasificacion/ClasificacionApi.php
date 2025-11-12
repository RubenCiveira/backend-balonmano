<?php

namespace Civi\Balonmano\Features\Clasificacion;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Jornada\Jornada;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Shared\Rest\CacheResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class ClasificacionApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/clasificacion', function ($group) {
            $group->get('', [ClasificacionApi::class, 'listByFase']);
        });
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/jornada/{jornada}/clasificacion', function ($group) {
            $group->get('', [ClasificacionApi::class, 'listByJornada']);
        });
    }

    public function __construct(private readonly ClasificacionRepository $repository, private readonly CacheResponse $cache)
    {
    }

    public function listByJornada(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        $competicion = new Competicion($args['competicion'], $args['competicion'], $categoria);
        $fase = new Fase($args['fase'], $args['fase'], $competicion);
        $jornada = new Jornada($args['jornada'], $args['jornada'], $fase);
        $value = $this->repository->clasificacion($jornada);
        return $this->cache->sendJson($request, $response, $value, 3_600);
    }

    public function listByFase(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        $competicion = new Competicion($args['competicion'], $args['competicion'], $categoria);
        $fase = new Fase($args['fase'], $args['fase'], $competicion);
        $value = $this->repository->clasificacion($fase);
        return $this->cache->sendJson($request, $response, $value, 3_600);
    }


}
