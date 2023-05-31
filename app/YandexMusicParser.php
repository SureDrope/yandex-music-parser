<?php

namespace App;

use Illuminate\Support\Facades\Http;
use App\Utils\UrlHandler;
use \Exception;

class YandexMusicParser
{
    private $artistId;

    public function __construct(string $url)
    {
        if (!UrlHandler::compareHosts($url, 'music.yandex.ru')) {
            throw new Exception('Invalid domain');
        };

        $matches = UrlHandler::findInUrl($url, '/\/artist\/(\d+)/');
        if ($matches) {
            $this->artistId = $matches[1];
        } else {
            throw new Exception('No artist ID found in the URL');
        }
    }

    public function parse()
    {
        $jsonData = $this->fetchJsonData();
        $artistData = $this->parseJson($jsonData);
        return $artistData;
    }

    public function fetchJsonData()
    {
        $yandexMusicUrl = 'https://music.yandex.ru/handlers/artist.jsx';

        $urlArguments = [
            'artist' => $this->artistId,
            'what' => 'tracks',
            'trackPage' => 0,
            'trackPageSize' => 100
        ];

        dd(file_get_contents($yandexMusicUrl . '?' . http_build_query($urlArguments)));
        // try {
        //     $response = Http::get($yandexMusicUrl, $urlArguments)->json();
        // } catch (Exception $e) {
        //     throw new Exception('Error: ' . $e->getMessage());
        // }
        // return $response
    }

    private function parseJson(object $jsonData)
    {
        $artistInfo = $jsonData->artist;
        $stats = $jsonData->stats;
        $albums = $jsonData->albums;
        $tracks = $jsonData->tracks;

        $artistData = (object) [
            'id' => $artistInfo->id,
            'name' => $artistInfo->name,
            'subscribers' => $artistInfo->likescount,
            'monthly_listeners' => $stats->lastMonthListeners,
            'albums_count' => count($albums)
        ];

        return $artistData;
    }
}
