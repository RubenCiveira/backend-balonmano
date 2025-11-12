<?php

namespace Civi\Balonmano\Shared\Image;

use DI\Container;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class ImageWrapper
{
    public static function register(App $app)
    {
        $app->group('/api/image/{image}', function ($group) {
            $group->get('', [ImageWrapper::class, 'get']);
        });
    }

    public function __construct(private readonly string $base)
    {
        // https://resultadosbalonmano.isquad.es/
    }

    public function publicUrl(string $url): string
    {
        return $this->base . '/api/image/' . base64_encode($url);
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // 1) Decodificar y validar la URL
        $url = base64_decode($args['image'] ?? '', true);
        if (!$url || 
                (!str_starts_with($url, '//balonmano.isquad.es/')
                && !str_starts_with($url, 'http://balonmano.isquad.es/')) ) {
            throw new InvalidArgumentException('URL '.$url.' no permitida');
        }
        $parts = parse_url($url);
        if ( ($parts['host'] ?? '') !== 'balonmano.isquad.es') {
            throw new InvalidArgumentException('Host '.$parts['host'].' no permitido');
        }
        if( !($parts['scheme'] ?? '' )) {
            $url = 'http:' . $url;
        }

        // 2) Descargar la imagen con timeout y UA
        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 8,
                'header'  => "User-Agent: SlimScraper/1.0\r\nAccept: image/*\r\n",
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);

        $bytes = @file_get_contents($url, false, $context);
        if ($bytes === false || strlen($bytes) === 0) {
            return $this->errorImageResponse($response, 502, 'No se pudo descargar la imagen');
        }

        // 3) Detectar MIME/medidas originales
        $info = @getimagesizefromstring($bytes);
        if ($info === false || !isset($info['mime']) || !str_starts_with($info['mime'], 'image/')) {
            return $this->errorImageResponse($response, 415, 'Contenido no es imagen');
        }
        $mime = $info['mime'];            // e.g. image/jpeg, image/png...
        $srcW = $info[0];
        $srcH = $info[1];

        // 4) Crear resource GD desde los bytes
        $src = @imagecreatefromstring($bytes);
        if (!$src) {
            return $this->errorImageResponse($response, 415, 'Imagen inválida');
        }

        // 5) Calcular tamaño destino (máx 120x120 manteniendo proporción)
        $max = 120;
        if( strpos($url, '/afiliacion/') ) {
            $max = 150;
        }
        $scale = min($max / $srcW, $max / $srcH, 1); // no ampliar si es más pequeña
        $dstW = (int) floor($srcW * $scale);
        $dstH = (int) floor($srcH * $scale);

        // 6) Redimensionar
        if ($scale < 1) {
            $dst = imagecreatetruecolor($dstW, $dstH);
            // Transparencia para PNG/WebP/GIF
            if (in_array($mime, ['image/png', 'image/webp', 'image/gif'], true)) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);
            }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
            imagedestroy($src);
        } else {
            // No hace falta escalar
            $dst   = $src;
            $dstW  = $srcW;
            $dstH  = $srcH;
        }

        // 7) Volcar a buffer manteniendo formato original cuando sea posible
        ob_start();
        $outMime = $mime;
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($dst, null, 85);
                break;
            case 'image/png':
                imagepng($dst);
                break;
            case 'image/gif':
                imagegif($dst);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    imagewebp($dst, null, 85);
                } else {
                    // Fallback a PNG si no hay soporte webp
                    imagepng($dst);
                    $outMime = 'image/png';
                }
                break;
            default:
                // Formatos raros → PNG
                imagepng($dst);
                $outMime = 'image/png';
                break;
        }
        imagedestroy($dst);
        $out = ob_get_clean();

        // 8) ETag + Cache 24h (y manejo If-None-Match)
        $etag = '"' . sha1($url . '|' . $dstW . 'x' . $dstH . '|' . substr($out, 0, 64)) . '"';
        $ifNone = $request->getHeaderLine('If-None-Match');
        if ($ifNone && trim($ifNone) === $etag) {
            return $response
                ->withStatus(304)
                ->withHeader('ETag', $etag)
                ->withHeader('Cache-Control', 'public, max-age=86400, immutable')
                ->withHeader('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        }

        // 9) Responder imagen + cabeceras de cache
        $response->getBody()->write($out);
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', $outMime)
            ->withHeader('Content-Length', (string) strlen($out))
            ->withHeader('ETag', $etag)
            ->withHeader('Cache-Control', 'public, max-age=86400, immutable')
            ->withHeader('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
    }

    /**
     * Devuelve una pequeña respuesta de error (PNG transparente 1x1) con cache corta.
     */
    private function errorImageResponse(ResponseInterface $response, int $status, string $msg): ResponseInterface
    {
        // PNG 1x1 transparente
        $img = imagecreatetruecolor(1, 1);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);
        ob_start();
        imagepng($img);
        imagedestroy($img);
        $out = ob_get_clean();

        $response->getBody()->write($out);
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Content-Length', (string) strlen($out))
            ->withHeader('Cache-Control', 'public, max-age=300')
            ->withHeader('X-Error-Message', $msg);
    }
}
