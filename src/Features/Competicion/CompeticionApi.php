<?php

namespace Civi\Balonmano\Features\Competicion;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Shared\Rest\CacheResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class CompeticionApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion', function ($group) {
            $group->get('', [CompeticionApi::class, 'list']);
        });
    }

    public function __construct(private readonly CompeticionRepository $repository, private readonly CacheResponse $cache)
    {
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        if ( $this->cache->askToRefresh( $request) ) {
            $this->repository->clearCache($categoria);
        }
        $value = $this->repository->competiciones($categoria);
        return $this->cache->sendJson($request, $response, $value, 36_000);
    }
}
