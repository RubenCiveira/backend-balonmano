<?php

namespace Civi\Balonmano\Features\Territorial;

use Slim\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Civi\Balonmano\Shared\Rest\CacheResponse;

class TerritorialApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial', function ($group) {
            $group->get('', [TerritorialApi::class, 'list']);
        });
    }

    public function __construct(private readonly TerritorialRepository $repository, private readonly CacheResponse $cache)
    {
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $_args): ResponseInterface
    {
        if ( $this->cache->askToRefresh( $request) ) {
            $this->repository->clearCache();
        }
        $value = $this->repository->territoriales();
        return $this->cache->sendJson($request, $response, $value, 36_000);
    }
}
