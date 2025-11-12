<?php

namespace Civi\Balonmano\Shared\Scrap;

use DateTimeImmutable;
use Civi\Balonmano\Features\Cancha\Cancha;
use Civi\Balonmano\Features\Categoria\Categoria;
use Civi\Balonmano\Features\Clasificacion\Clasificacion;
use Civi\Balonmano\Features\Competicion\Competicion;
use Civi\Balonmano\Features\Equipo\Equipo;
use Civi\Balonmano\Features\Fase\Fase;
use Civi\Balonmano\Features\Jornada\Jornada;
use Civi\Balonmano\Features\Partido\Partido;
use Civi\Balonmano\Features\Provincial\Provincial;
use Civi\Balonmano\Features\Temporada\Temporada;
use Civi\Balonmano\Features\Territorial\Territorial;
use Civi\Balonmano\Shared\Image\ImageWrapper;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class Extractor
{
    public function __construct(private readonly ImageWrapper $imageWrapper)
    {
    }
    public function extractClasificacion(Jornada|Fase $filter)
    {
        if ($filter instanceof Jornada) {
            return $this->extractClasificacionPorJornada($filter);
        } else {
            return $this->extractClasificacionPorFase($filter);
        }
    }

    public function extractPartidos(Jornada|Fase $filter)
    {
        if ($filter instanceof Jornada) {
            return $this->extractPartidosForJornada($filter);
        } else {
            return $this->extractPartidosPorFase($filter);
        }
    }

    public function extractJornadaActual(Fase $fase)
    {
        $crawler = $this->crawler("id={$fase->code}", "competicion");
        $number = $crawler->filter('.jornada_actual')->text();
        return new Jornada($number, $number, $fase);
    }

    public function extractJornadas(Fase $fase)
    {
        $crawler = $this->crawler("id={$fase->code}", "competicion");
        $rows = $crawler->filter(".lista_jornadas .capa_jornada a");
        $data = [];
        $rows->each(function (Crawler $row) use (&$data, $fase) {
            $data[] = new Jornada(
                $row->text(), // base64_encode($row->attr('href')),
                $row->text(),
                $fase
            );
        });
        return $data;
    }

    public function extractTerritoriales()
    {
        $rows = $this->extractSelector("", "territorial");
        $data = [];
        foreach ($rows as $k => $v) {
            $data[] = new Territorial($k, $v);
        }
        return $data;
    }

    /**
     * @return Temporada[]
     */
    public function extractTemporadas(Territorial $territorial, ?Provincial $provincial = null)
    {
        $rows = $this->extractSelector("id_territorial={$territorial->code}", "temporadas");
        $data = [];
        foreach ($rows as $k => $v) {
            $data[] = new Temporada($k, $v, $territorial, $provincial);
        }
        return $data;
    }

    public function extractCategorias(Temporada $temporada)
    {
        $rows = $this->extractSelector("id_territorial={$temporada->territorial->code}&id_temp={$temporada->code}", "categorias");
        $data = [];
        foreach ($rows as $k => $v) {
            $data[] = new Categoria($k, $v, $temporada);
        }
        return $data;
    }

    public function extractCompeticion(Categoria $categoria)
    {
        $rows = $this->extractSelector("id_categoria={$categoria->code}&id_territorial={$categoria->temporada->territorial->code}&id_temp={$categoria->temporada->code}", "competiciones");
        $data = [];
        foreach ($rows as $k => $v) {
            $data[] = new Competicion($k, $v, $categoria);
        }
        return $data;
    }

    public function extractFase(Competicion $competicion)
    {
        $rows = $this->extractSelector("id_competicion={$competicion->code}&id_categoria={$competicion->categoria->code}&id_territorial={$competicion->categoria->temporada->territorial->code}&id_temp={$competicion->categoria->temporada->code}", "torneos");
        $data = [];
        foreach ($rows as $k => $v) {
            $data[] = new Fase($k, $v, $competicion);
        }
        return $data;
    }

    private function crawler($url, $of): Crawler
    {
        return $this->crawFormUrl("https://resultadosbalonmano.isquad.es/{$of}.php?id_superficie=1&seleccion=0&id_ambito=0&{$url}");
    }

    private function crawFormUrl($url): Crawler
    {
        $client = HttpClient::create([
                   'headers' => ['User-Agent' => 'Mozilla/5.0 (Slim Scraper)'],
                   'timeout' => 10,
               ]);

        $html = $client->request('GET', $url)->getContent();
        return new Crawler($html);
    }

    private function extractSelector(string $url, string $id)
    {
        $crawler = $this->crawler($url, "competicion");
        $rows = $crawler->filter("#{$id} option");
        $data = [];
        $rows->each(function (Crawler $row) use (&$data) {
            $data[ $row->attr('value') ] = $row->text();
        });
        return $data;
    }

    private function extractPartidosPorFase(Fase $fase)
    {
        $crawler = $this->crawler("id={$fase->code}", "clasificacion");
        $jid = $crawler->filter('.jornada_actual')->text();
        $jornada = new Jornada($jid, $jid, $fase);
        return $this->extractPartidosForJornada($jornada);
    }

    private function extractPartidosForJornada(Jornada $jornada)
    {
        $data = [];
        $crawler = $this->crawler("id={$jornada->fase->code}&jornada={$jornada->label}", "competicion");
        $rows = $crawler->filter(".partido");
        if ($rows->count() > 0) {
            $actual = DateTimeImmutable::createFromFormat('d-m-Y H:i', $crawler->filter(".fecha-jornada-actual")->text() . ' 07:00');

            $rows->each(function (Crawler $row) use (&$data, $jornada, $actual) {
                $result = [];
                $id = $row->attr('data-id');
                $territorial = $jornada->territorial();
                $equipos = $row->filter('.nombres-equipos a');
                $escudos = $row->filter('.escudos-partido img');

                if ($equipos->count() > 0 && $escudos->count() > 0) {
                    $local = true;
                    $equipos->each(function (Crawler $equipo) use (&$result, &$local) {
                        if ($local) {
                            $result['equipoLocal'] = [ $equipo->text(), base64_encode($equipo->attr('href')) ];
                        } else {
                            $result['equipoVisitante'] = [ $equipo->text(), base64_encode($equipo->attr('href')) ];
                        }
                        $local = false;
                    });
                    $local = true;
                    $escudos->each(function (Crawler $escudo) use (&$result, &$local) {
                        if ($local) {
                            $result['equipoLocal'][] = $this->imageWrapper->publicUrl($escudo->attr('src'));
                        } else {
                            $result['equipoVisitante'][] = $this->imageWrapper->publicUrl($escudo->attr('src'));
                        }
                        $local = false;
                    });
                    $fecha = $row->filter('td:nth-child(3)');
                    $fecha = $fecha->count() > 0 ? $fecha->text() : false;
                    $lugar = $row->filter('td:nth-child(4) a');
                    $data[] = new Partido(
                        code: $id,
                        label: $id,
                        jornada: $jornada,
                        local: new Equipo($result['equipoLocal'][1], $result['equipoLocal'][0], $territorial, $result['equipoLocal'][2]),
                        visitante: new Equipo($result['equipoVisitante'][1], $result['equipoVisitante'][0], $territorial, $result['equipoVisitante'][2]),
                        estado: $row->filter('td:nth-child(5)')->text(),
                        puntosLocal: intval($row->filter('.col-marcador .local')->text()),
                        puntosVisitante: intval($row->filter('.col-marcador .visitante')->text()),
                        fecha: $fecha ? DateTimeImmutable::createFromFormat('d/m/Y H:i', $fecha) : $actual,
                        lugar: $lugar && $lugar->count() > 0 ? new Cancha(base64_encode($lugar->attr('onclick')), $lugar->text()) : null
                    );
                }
            });
        }
        return $data;
    }

    private function extractClasificacionPorFase(Fase $fase)
    {
        $crawler = $this->crawler("id={$fase->code}", "clasificacion");
        $jid = $crawler->filter('.jornada_actual')->text();
        $jornada = new Jornada($jid, $jid, $fase);
        return $this->extractClasificacionPorJornada($jornada);
    }

    private function extractClasificacionPorJornada(Jornada $jornada)
    {
        $data = [];
        $crawler = $this->crawler("id={$jornada->fase->code}&jornada={$jornada->label}", "clasificacion");
        $rows = $crawler->filter(".clasificacion tbody tr");
        if ($rows->count() > 0) {
            $rows->each(function (Crawler $row) use (&$data, $jornada) {
                $equipo = $row->filter('.nombre-clasi a');
                $logoEquipo = $this->imageWrapper->publicUrl($equipo->filter('.image-content img')->attr('src'));
                $cambio = 0;
                $nombre = trim( $equipo->text() );
                if (preg_match('/^\h*([+\-\x{2212}\x{2013}\x{2014}▲▼↑↓]?)\h*(\d+)\h*(.*)$/u', $nombre, $m)) {
                    [$_all, $signo, $cambio, $nombre] = $m;
                    // normaliza el signo unicode a '+'/'-'
                    if ($signo === "\xE2\x88\x92") {
                        $signo = '-';
                    } // U+2212
                    $cambio = trim($signo) . trim($cambio);
                    // $nombre tiene el texto limpio
                }
                $id = $jornada->uid() . "_" . $nombre;
                //  = preg_replace('/^\d+\s*/', '', $equipo->text());
                $data[] = new Clasificacion(
                    code: $id,
                    label: $id,
                    jornada: $jornada,
                    equipo: new Equipo(base64_encode($equipo->attr('href')), $nombre, $jornada->territorial(), $logoEquipo),
                    posicion: intval($row->filter('td:nth-child(1)')->text()),
                    puntos: intval($row->filter('td:nth-child(4)')->text()),
                    ganados: intval($row->filter('td:nth-child(6) a')->text()),
                    empatados: intval($row->filter('td:nth-child(7) a')->text()),
                    perdidos: intval($row->filter('td:nth-child(8) a')->text()),
                    golesMarcados: intval($row->filter('td:nth-child(9)')->text()),
                    golesRecividos: intval($row->filter('td:nth-child(10)')->text()),
                    diferencia: intval($cambio)
                );
            });
        }
        return $data;
    }

}
