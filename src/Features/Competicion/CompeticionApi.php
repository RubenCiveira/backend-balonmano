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

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        if ('true' === $request->getQueryParams()['refresh']) {
            $this->repository->clearCache($categoria);
        }
        $value = $this->repository->competiciones($categoria);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
