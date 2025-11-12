<?php

namespace Civi\Balonmano\Features\Equipo;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Shared\Rest\CacheResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class EquipoApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/equipo/{codigo}', function ($group) {
            $group->get('', [EquipoApi::class, 'retrieve']);
        });
    }

    public function __construct(private readonly EquipoRepository $repository, private readonly CacheResponse $cache)
    {
    }

    public function retrieve(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $codigo = $args['codigo'];
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        $competicion = new Competicion($args['competicion'], $args['competicion'], $categoria);
        $fase = new Fase($args['fase'], $args['fase'], $competicion);
        $equipo = new Equipo(code: $codigo, label: $codigo, fase: $fase, logo: $codigo);
        if ( $this->cache->askToRefresh( $request) ) {
            $this->repository->clearCache($equipo);
        }
        $value = $this->repository->detalles($equipo);
        return $this->cache->sendJson($request, $response, $value, 36_000);
    }
}
