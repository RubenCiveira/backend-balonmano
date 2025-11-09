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

    public function list(ServerRequestInterface $_request, ResponseInterface $response, array $_args): ResponseInterface
    {
        $territorial = new Territorial($_args['territorial'], $_args['territorial']);
        $temporada = new Temporada($_args['temporada'], $_args['temporada'], $territorial);
        $categoria = new Categoria($_args['categoria'], $_args['categoria'], $temporada);
        $competicion = new Competicion($_args['competicion'], $_args['competicion'], $categoria);
        $value = $this->repository->fases($competicion);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
