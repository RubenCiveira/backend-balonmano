<?php

namespace Civi\Balonmano\Shared\Rest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CacheResponse
{
    public function askToRefresh(ServerRequestInterface $request) {
        $params = $request->getQueryParams();
        return isset($params['refresh']) && $params['refresh'] === 'true';
    }
    public function sendJson(
        ServerRequestInterface $request,
        ResponseInterface $response,
        mixed $value,
        int $maxAgeSeconds
    ): ResponseInterface {
        $body = json_encode($value);
        $etag = '"' . sha1($body) . '"';
        $ifNoneMatch = $request->getHeaderLine('If-None-Match');
        $cacheControl = sprintf('public, max-age=%d, stale-while-revalidate=30', $maxAgeSeconds);
        if ($ifNoneMatch !== '' && $this->matchEtags($ifNoneMatch, $etag)) {
            return $response
                        ->withStatus(304)
                        ->withHeader('ETag', $etag)
                        ->withHeader('Cache-Control', $cacheControl)
                        ->withHeader('Vary', 'Accept-Encoding'); // añade más si procede (Authorization, Accept, etc.)
        } else {
            $response->getBody()->write($body);
            return $response
                        ->withStatus(200)
                        ->withHeader('ETag', $etag)
                        ->withHeader('Cache-Control', $cacheControl)
                        ->withHeader('Vary', 'Accept-Encoding') // añade más si procede (Authorization, Accept, etc.)
                        ->withHeader('Content-Type', 'application/json');
        }
    }

    private function matchEtags(string $ifNoneMatch, string $etag): bool
    {
        // If-None-Match puede traer varios valores separados por coma
        foreach (array_map('trim', explode(',', $ifNoneMatch)) as $candidate) {
            if ($candidate === '*' || $candidate === $etag) {
                return true;
            }
            // Coincidencia weak: W/"hash" vs "hash"
            if (str_starts_with($candidate, 'W/') && substr($candidate, 2) === $etag) {
                return true;
            }
            if (str_starts_with($etag, 'W/') && substr($etag, 2) === $candidate) {
                return true;
            }
        }
        return false;
    }
}
