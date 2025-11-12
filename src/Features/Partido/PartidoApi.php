<?php

namespace Civi\Balonmano\Features\Partido;

use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Jornada\Jornada;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Shared\Rest\CacheResponse;
use DateTime;
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

    public function __construct(private readonly PartidoRepository $repository, private readonly CacheResponse $cache)
    {
    }

    public function listByJornada(ServerRequestInterface $request, ResponseInterface $response, array $_args): ResponseInterface
    {
        $territorial = new Territorial($_args['territorial'], $_args['territorial']);
        $temporada = new Temporada($_args['temporada'], $_args['temporada'], $territorial);
        $categoria = new Categoria($_args['categoria'], $_args['categoria'], $temporada);
        $competicion = new Competicion($_args['competicion'], $_args['competicion'], $categoria);
        $fase = new Fase($_args['fase'], $_args['fase'], $competicion);
        $jornada = new Jornada($_args['jornada'], $_args['jornada'], $fase);
        $value = array_map( function($row) {
            $data = get_object_vars( $row );
            $data['fecha'] = $row->fecha->format(DateTime::ATOM);
            return $data;
        }, $this->repository->partidos($jornada) );
        return $this->cache->sendJson($request, $response, $value, 3_600);
    }
    public function listByFase(ServerRequestInterface $request, ResponseInterface $response, array $_args): ResponseInterface
    {
        $territorial = new Territorial($_args['territorial'], $_args['territorial']);
        $temporada = new Temporada($_args['temporada'], $_args['temporada'], $territorial);
        $categoria = new Categoria($_args['categoria'], $_args['categoria'], $temporada);
        $competicion = new Competicion($_args['competicion'], $_args['competicion'], $categoria);
        $fase = new Fase($_args['fase'], $_args['fase'], $competicion);
        $value = array_map( function($row) {
            $data = get_object_vars( $row );
            $data['fecha'] = $row->fecha->format(DateTime::ATOM);
            return $data;
        }, $this->repository->partidos($fase));
        return $this->cache->sendJson($request, $response, $value, 3_600);
    }
}
