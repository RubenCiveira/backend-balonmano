<?php

namespace Civi\Balonmano\Features\Competicion;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
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

    public function __construct(private readonly CompeticionRepository $repository)
    {
    }

    public function list(ServerRequestInterface $_request, ResponseInterface $response, array $_args): ResponseInterface
    {
        $territorial = new Territorial($_args['territorial'], $_args['territorial']);
        $temporada = new Temporada($_args['temporada'], $_args['temporada'], $territorial);
        $categoria = new Categoria($_args['categoria'], $_args['categoria'], $temporada);
        $value = $this->repository->competiciones($categoria);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
