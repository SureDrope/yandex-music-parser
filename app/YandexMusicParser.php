<?php

namespace App;

use Illuminate\Support\Facades\Http;
use App\Utils\UrlHandler;
use App\Models\Artist;
use App\Models\Track;
use \Exception;

class YandexMusicParser
{
    private $artistId;
    private $isArtistInDb = false;

    private $artist;
    private $tracks;

    public function __construct(string $url)
    {
        if (!UrlHandler::compareHosts($url, 'music.yandex.ru')) {
            throw new Exception('Invalid domain');
        };

        // extract artist ID from url
        $matches = UrlHandler::findInUrl($url, '/\/artist\/(\d+)/');
        if ($matches) {
            $this->artistId = $matches[1];
        } else {
            throw new Exception('No artist ID found in the URL');
        }

        $isArtistInDb = Artist::where('id', $this->artistId)->first();
        if ($isArtistInDb) $this->isArtistInDb = true;

        $this->getData();
        if (!$this->isArtistInDb) $this->saveArtist();
        $this->saveTracks();
    }

    private function getData()
    {
        $jsonData = $this->fetchJson();
        $this->parseJson($jsonData);
    }

    private function fetchJson()
    {
        $yandexMusicUrl = 'https://music.yandex.ru/handlers/artist.jsx';

        $urlArguments = [
            'artist' => $this->artistId,
            'what' => 'tracks',
            'trackPage' => 0,
            'trackPageSize' => 100
        ];

        $headers = [
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'en-US',
            'Cache-Control' => 'max-age=0',
            'Connection' => 'keep-alive',
            'Cookie' => 'i=DnlWT7ncEvXFuUih31ylgGcNWPuID4y2zOS0gM9BKTl+NH60M5TNmlp71JkEy3DsE1WMDAaCkFaNJGqghQNq8Nuh0Ug=; yandexuid=3826378871681486198; skid=4639314151681487008; is_gdpr=0; is_gdpr_b=CIHuMRDasQE=; device_id=beabfe67f3081fe6635d6259357b9ae590d39a2b9; active-browser-timestamp=1685543663568; _yasc=GoJWAE/Dhy0oGfNteRYIgCPr+jPoJYZLTp4820T9aFFb5w1PGoxmb+v+VXTP7uBIyQ==',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Sec-GPC' => '1',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36',
            'sec-ch-ua' => '"Brave";v="113", "Chromium";v="113", "Not-A.Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Windows"',
        ];
        try {
            $response = Http::withHeaders($headers)->get($yandexMusicUrl, $urlArguments);
        } catch (Exception $e) {
            throw new Exception('Error: ' . $e->getMessage());
        }
        return $response->json();
    }

    private function parseJson(array $jsonData)
    {
        $artistInfo = $jsonData['artist'];
        $stats = $jsonData['stats'];
        $albums = $jsonData['albums'];
        $tracks = $jsonData['tracks'];
        $this->tracks = array_map(function ($track) {
            return [
                'id' => $track['id'],
                'artist_id' => $this->artistId,
                'name' => $track['title'],
                'duration_ms' => $track['durationMs'],
            ];
        }, $tracks);

        $this->artist = [
            'id' => $artistInfo['id'],
            'name' => $artistInfo['name'],
            'subscribers' => $artistInfo['likesCount'],
            'monthly_listeners' => $stats['lastMonthListeners'],
            'albums_count' => count($albums)
        ];
    }
    private function saveArtist()
    {
        Artist::create($this->artist);
    }
    private function saveTracks()
    {
        Track::insert($this->tracks);
    }
}
