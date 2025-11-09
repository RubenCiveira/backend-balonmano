<?php

declare(strict_types=1);

namespace Civi\Balonmano\Shared\Middelware;

use Slim\App;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @api
 */
class CorsMiddleware
{
    public static function register(App $app)
    {
        $app->add(CorsMiddleware::class);
        $app->options('/{routes:.+}', function ($request, $response, $args) {
            return $response;
        });
    }
    public function __construct()
    {

    }
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = $this->addHeaders(new Response(), $request);
            return $response->withStatus(204);
        } else {
            return $this->addHeaders($handler->handle($request), $request);
        }
    }

    private function addHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $origin = $request->hasHeader('Origin') ? $request->getHeader('Origin') : '*';
        return $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept-Language, traceparent, x-api-key')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Expose-Headers', 'Content-Disposition')
            ->withHeader('Access-Control-Max-Age', '86400');
    }
}
