<?php

namespace Civi\Balonmano\Features\Fase;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class FaseApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase', function ($group) {
            $group->get('', [FaseApi::class, 'list']);
        });
    }

    public function __construct(private readonly FaseRepository $repository)
    {
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        $competicion = new Competicion($args['competicion'], $args['competicion'], $categoria);
        if ('true' === $request->getQueryParams()['refresh']) {
            $this->repository->clearCache($competicion);
        }
        $value = $this->repository->fases($competicion);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
