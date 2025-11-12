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
        if ('true' === $request->getQueryParams()['refresh']) {
            $this->repository->clearCache();
        }
        $value = $this->repository->territoriales();
        $body = json_encode($value);
        $response->getBody()->write($body);
        return $this->cache->sendJson($request, $response, $value, 3600);
    }
}
