<?php

namespace Civi\Balonmano\Features\Temporada;

use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Shared\Rest\CacheResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class TemporadaApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada', function ($group) {
            $group->get('', [TemporadaApi::class, 'list']);
        });
    }

    public function __construct(private readonly TemporadaRepository $repository, private readonly CacheResponse $cache)
    {
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        if ( $this->cache->askToRefresh( $request) ) {
            $this->repository->clearCache($territorial);
        }
        $value = $this->repository->temporadas($territorial);
        return $this->cache->sendJson($request, $response, $value, 36_000);
    }
}
