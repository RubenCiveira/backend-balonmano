<?php

use Psr\SimpleCache\CacheInterface;
use DI\Container;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use Symfony\Component\RateLimiter\Storage\StorageInterface;

use Civi\Balonmano\Features\Categoria\CategoriaApi;
use Civi\Balonmano\Features\Clasificacion\ClasificacionApi;
use Civi\Balonmano\Features\Clasificacion\ClasificacionRepository;
use Civi\Balonmano\Features\Competicion\CompeticionApi;
use Civi\Balonmano\Features\Competicion\CompeticionRepository;
use Civi\Balonmano\Features\Fase\FaseApi;
use Civi\Balonmano\Features\Fase\FaseRepository;
use Civi\Balonmano\Features\Jornada\JornadaApi;
use Civi\Balonmano\Features\Jornada\JornadaRepository;
use Civi\Balonmano\Features\Partido\PartidoApi;
use Civi\Balonmano\Features\Partido\PartidoRepository;
use Civi\Balonmano\Features\Temporada\TemporadaApi;
use Civi\Balonmano\Features\Temporada\TemporadaRepository;
use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Features\Territorial\TerritorialApi;
use Civi\Balonmano\Shared\Image\ImageWrapper;
use Civi\Balonmano\Shared\Middelware\CorsMiddleware;
use Civi\Balonmano\Shared\Middelware\HttpCompressionMiddleware;
use Civi\Balonmano\Shared\Middelware\RateLimitMiddleware;
use Civi\Balonmano\Shared\Rest\Errors;

require_once __DIR__ . '/../vendor/autoload.php';
// Opcional: definir base path si tu app no está en "/"
$scriptName = $_SERVER['SCRIPT_NAME']; // Devuelve algo como "/midashboard/index.php"
$basePath = str_replace('/index.php', '', $scriptName); // "/midashboard"
$useCache = true;

$builder = new ContainerBuilder();
$builder->addDefinitions(['basePath' => $basePath]);
$builder->addDefinitions(['useCache' => $useCache]);
$builder->addDefinitions([
    ImageWrapper::class => function (Container $container) {
         $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
        $scheme = $isHttps ? 'https' : 'http';

        // --- Detectar host y puerto
        $host = $_SERVER['HTTP_HOST']
            ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $port = (int)($_SERVER['SERVER_PORT'] ?? 80);

        // Si el host ya incluye puerto (HTTP_HOST suele incluirlo), no lo añadimos
        if (!str_contains($host, ':') && !in_array($port, [80, 443], true)) {
            $host .= ':' . $port;
        }
        $base = sprintf('%s://%s%s', $scheme, $host, $container->get('basePath'));
        return new ImageWrapper( $base );
    }
]);
$builder->addDefinitions([
    CacheInterface::class => function (Container $container) {
        $useCache = $container->get('useCache');
        $interval = new DateInterval('PT2H');
        $now = new \DateTimeImmutable();
        $future = $now->add($interval);
        $defaultLifetime = $future->getTimestamp() - $now->getTimestamp();
        return new Psr16Cache(
            $useCache ? new RedisAdapter(new \Redis(), 'balonmano', $defaultLifetime) : new NullAdapter()
        );
    }
]);
$builder->addDefinitions([
    StorageInterface::class => function () {
        return new CacheStorage(new RedisAdapter(new \Redis()));
    }
]);

$builder->useAutowiring(true);
$builder->useAttributes(true);
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->setBasePath($basePath);

// Middleware para parsear json
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$app->add(CorsMiddleware::class);
$app->add(HttpCompressionMiddleware::class);
$app->add(RateLimitMiddleware::class);

Errors::handle($app->addErrorMiddleware(true, true, true));

ImageWrapper::register($app);
TerritorialApi::register($app);
TemporadaApi::register($app);
CategoriaApi::register($app);
CompeticionApi::register($app);
FaseApi::register($app);
JornadaApi::register($app);
PartidoApi::register($app);
ClasificacionApi::register($app);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->get('/', function () use ($container) {
    $t = new Territorial(20, 'Gallega');

    $temporadasRepo = $container->get(TemporadaRepository::class);
    // $temporadasRepo = new TemporadaRepository($cache, $extractor);
    $row = $temporadasRepo->temporadas($t);
    // echo "<pre>";
    // print_r( $row );
    // echo "</pre>";
    $cats = $temporadasRepo->categorias($row[0]);
    // echo "<pre>";
    // print_r( $cats );
    // echo "</pre>";

    $competicionesRepo = $container->get(CompeticionRepository::class);
    $comps = $competicionesRepo->competiciones($cats[0]);

    // echo "<pre>";
    // print_r( $comps );
    // echo "</pre>";

    $faseRepo = $container->get(FaseRepository::class);
    $fases = $faseRepo->fases($comps[0]);
    foreach ($fases as $fase) {
        echo "<p>" . $fase->label;
    }

    $jornadasRepo = $container->get(JornadaRepository::class);
    $jornadas = $jornadasRepo->jornadas($fases[3]);

    $partidosRepo = $container->get(PartidoRepository::class);
    $partidos = $partidosRepo->partidos($jornadas[0]);
    echo "<table>";
    foreach ($partidos as $p) {
        echo "<tr><td>";
        // print_r( $p );
        echo $p->fecha->format(DateTime::ATOM) . " en ". $p->lugar->label ."</br>";

        echo $p->local->label;
        echo "<img style=\"width: 40px; height: 40px;\" src=\"".$p->local->logo."\" />";
        if ($p->isEnded()) {
            echo "(" . $p->puntosLocal . ")";
        }
        echo " vs ";
        if ($p->isEnded()) {
            echo "(" . $p->puntosVisitante . ")";
        }
        echo "<img style=\"width: 40px; height: 40px;\" src=\"".$p->visitante->logo."\" />";
        echo $p->visitante->label;
        echo "</td></tr>";
    }
    echo "</table>";

    $clasifRepo = $container->get(ClasificacionRepository::class);
    $clasificaciones = $clasifRepo->clasificacion($jornadas[0]);
    echo "<table>";
    foreach ($clasificaciones as $clasificacion) {
        echo "<tr><td>";
        echo $clasificacion->posicion . "(" . $clasificacion->puntos . 'pts)';
        echo "</td><td>";
        echo "<img style=\"width: 40px; height: 40px;\" src=\"".$clasificacion->equipo->logo."\" />";
        echo $clasificacion->equipo->label;
        echo "</td><td>";
        echo $clasificacion->ganados . "/" . $clasificacion->empatados . "/" . $clasificacion->perdidos;
        echo "</td><td>";
        echo "+" . $clasificacion->golesMarcados . ' / - ' . $clasificacion->golesRecividos;
        echo "</td></tr>";
    }
    echo "</table>";
});

$app->run();

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}
