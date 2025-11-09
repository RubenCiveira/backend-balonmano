<?php

namespace Civi\Balonmano\Features\Territorial;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class TerritorialApi
{
    public static function register(App $app)
    {
        $app->group('/api/territorial', function ($group) {
            $group->get('', [TerritorialApi::class, 'list']);
        });
    }

    public function __construct(private readonly TerritorialRepository $repository)
    {
    }

    public function list(ServerRequestInterface $_request, ResponseInterface $response, array $_args): ResponseInterface
    {
        $value = $this->repository->territoriales();
        $response->getBody()->write(json_encode($value));
        return $response->withStatus(200)
          ->withHeader('Content-Type', 'application/json');
    }
}
