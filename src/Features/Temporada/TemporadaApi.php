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

    public function list(ServerRequestInterface $_request, ResponseInterface $response, array $_args): ResponseInterface
    {
        $territorial = new Territorial($_args['territorial'], $_args['territorial']);
        $value = $this->repository->temporadas($territorial);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
