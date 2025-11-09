<?php

namespace Civi\Balonmano\Shared\Rest;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Middleware\ErrorMiddleware;

class Errors
{
    public static function handle(ErrorMiddleware $middle)
    {
        $middle->setErrorHandler(Exception::class, function (ServerRequestInterface $request, Exception $exception): ResponseInterface {
            echo $exception->getTraceAsString();
            die();
        });

    }
}
