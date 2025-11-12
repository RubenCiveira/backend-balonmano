<?php

namespace Civi\Balonmano\Features\Jornada;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class JornadaApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/jornada', function ($group) {
            $group->get('', [JornadaApi::class, 'list']);
        });
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria/{categoria}/competicion/{competicion}/fase/{fase}/jornada-actual', function ($group) {
            $group->get('', [JornadaApi::class, 'listActual']);
        });
    }

    public function __construct(private readonly JornadaRepository $repository)
    {
    }

    public function listActual(ServerRequestInterface $_request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        $competicion = new Competicion($args['competicion'], $args['competicion'], $categoria);
        $fase = new Fase($args['fase'], $args['fase'], $competicion);
        $value = $this->repository->jornadaActual($fase);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        $categoria = new Categoria($args['categoria'], $args['categoria'], $temporada);
        $competicion = new Competicion($args['competicion'], $args['competicion'], $categoria);
        $fase = new Fase($args['fase'], $args['fase'], $competicion);
        if ('true' === $request->getQueryParams()['refresh']) {
            $this->repository->clearCache($fase);
        }
        $value = $this->repository->jornadas($fase);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
