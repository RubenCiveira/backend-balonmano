<?php

namespace Civi\Balonmano\Features\Jornada;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Shared\Rest\CacheResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class JornadaApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/jornada', function ($group) {
            $group->get('', [JornadaApi::class, 'list']);
        });
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/jornada-actual', function ($group) {
            $group->get('', [JornadaApi::class, 'listActual']);
        });
    }

    public function __construct(private readonly JornadaRepository $repository, private readonly CacheResponse $cache)
    {
    }

    public function listActual(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        $competicion = new Competicion($args['competicion'], $args['competicion'], $categoria);
        $fase = new Fase($args['fase'], $args['fase'], $competicion);
        $value = $this->repository->jornadaActual($fase);
        return $this->cache->sendJson($request, $response, $value, 3_600);
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        $competicion = new Competicion($args['competicion'], $args['competicion'], $categoria);
        $fase = new Fase($args['fase'], $args['fase'], $competicion);
        if ( $this->cache->askToRefresh( $request) ) {
            $this->repository->clearCache($fase);
        }
        $value = $this->repository->jornadas($fase);
        return $this->cache->sendJson($request, $response, $value, 3_600);
    }
}
