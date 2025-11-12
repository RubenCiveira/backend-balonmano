<?php

namespace Civi\Balonmano\Features\Categoria;

use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class CategoriaApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial/{territorial}/temporada/{temporada}/categoria', function ($group) {
            $group->get('', [CategoriaApi::class, 'list']);
        });
    }

    public function __construct(private readonly CategoriaRepository $repository)
    {
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $territorial = new Territorial($args['territorial'], $args['territorial']);
        $temporada = new Temporada($args['temporada'], $args['temporada'], $territorial);
        if ('true' === $request->getQueryParams()['refresh']) {
            $this->repository->clearCache($temporada);
        }
        $value = $this->repository->categorias($temporada);
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
