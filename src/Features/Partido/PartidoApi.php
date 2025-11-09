<?php

namespace Civi\Balonmano\Features\Partido;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Jornada\Jornada;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class PartidoApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/partido', function ($group) {
            $group->get('', [PartidoApi::class, 'listByFase']);
        });
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/jornada/{jornada}/partido', function ($group) {
            $group->get('', [PartidoApi::class, 'listByJornada']);
        });
    }

    public function __construct(private readonly PartidoRepository $repository)
    {
    }

    public function listByJornada(ServerRequestInterface $_request, ResponseInterface $response, array $_args): ResponseInterface
    {
        $territorial = new Territorial($_args['territorial'], $_args['territorial']);
        $temporada = new Temporada($_args['temporada'], $_args['temporada'], $territorial);
        $categoria = new Categoria($_args['categoria'], $_args['categoria'], $temporada);
        $competicion = new Competicion($_args['competicion'], $_args['competicion'], $categoria);
        $fase = new Fase($_args['fase'], $_args['fase'], $competicion);
        $jornada = new Jornada($_args['jornada'], $_args['jornada'], $fase);
        $value = $this->repository->partidos($jornada);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
    public function listByFase(ServerRequestInterface $_request, ResponseInterface $response, array $_args): ResponseInterface
    {
        $territorial = new Territorial($_args['territorial'], $_args['territorial']);
        $temporada = new Temporada($_args['temporada'], $_args['temporada'], $territorial);
        $categoria = new Categoria($_args['categoria'], $_args['categoria'], $temporada);
        $competicion = new Competicion($_args['competicion'], $_args['competicion'], $categoria);
        $fase = new Fase($_args['fase'], $_args['fase'], $competicion);
        $value = $this->repository->partidos($fase);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
