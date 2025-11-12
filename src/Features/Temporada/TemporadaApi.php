<?php

namespace Civi\Balonmano\Features\Temporada;

use Civi\Balonmano\Features\Territorial\Territorial;
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

    public function __construct(private readonly TemporadaRepository $repository)
    {
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        if ('true' === $request->getQueryParams()['refresh']) {
            $this->repository->clearCache($territorial);
        }
        $value = $this->repository->temporadas($territorial);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
