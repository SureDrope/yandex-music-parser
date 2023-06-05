<?php

namespace App;

use Illuminate\Support\Facades\Http;
use App\Utils\UrlHandler;
use App\Models\Artist;
use \Exception;

class YandexMusicParser
{
    const YANDEX_MUSIC_HOST = 'music.yandex.ru';
    const YANDEX_MUSIC_API_URL =  'https://music.yandex.ru/handlers/artist.jsx';
    const MAX_AMOUNT_OF_TRACKS_TO_FETCH = 1000000;

    private string $artistId;
    private $artistInDb;

    private array $fetchedArtist;
    private array $fetchedTracks;

    public function __construct(string $url)
    {
        if (!UrlHandler::compareHosts($url, self::YANDEX_MUSIC_HOST)) {
            throw new Exception('Invalid domain');
        };

        // extract artist ID from url
        $matches = UrlHandler::findInUrl($url, '/\/artist\/(\d+)/');
        if ($matches) {
            $this->artistId = $matches[1];
        } else {
            throw new Exception('No artist ID found in the URL');
        }

        $this->artistInDb = Artist::find($this->artistId);

        $this->fetchAndParseJson();

        $this->saveArtist();
        $this->saveTracks();
    }

    private function fetchAndParseJson()
    {
        $data = $this->fetchJson();
        $this->parseJson($data);
    }
    private function fetchJson()
    {
        $urlArguments = [
            'artist' => $this->artistId,
            'what' => 'tracks',
            'trackPage' => 0,
            'trackPageSize' => self::MAX_AMOUNT_OF_TRACKS_TO_FETCH,
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
            $response = Http::withHeaders($headers)
                ->get(self::YANDEX_MUSIC_API_URL, $urlArguments);
        } catch (Exception $e) {
            throw new Exception('Error: ' . $e->getMessage());
        }
        return $response->json();
    }

    private function parseJson(array $data)
    {
        $artist = $data['artist'];
        $stats = $data['stats'];
        $albums = $data['albums'];
        $tracks = $data['tracks'];
        $this->fetchedTracks = array_map(function ($track) {
            return [
                'id' => $track['id'],
                'artist_id' => $this->artistId,
                'name' => $track['title'],
                'duration_ms' => $track['durationMs'],
            ];
        }, $tracks);

        $this->fetchedArtist = [
            'name' => $artist['name'],
            'subscribers' => $artist['likesCount'],
            'monthly_listeners' => $stats['lastMonthListeners'],
            'albums_count' => count($albums),
        ];
    }
    private function saveArtist(): void
    {
        if (!$this->artistInDb) {
            Artist::create(
                array_merge(
                    ['id' => $this->artistId],
                    $this->fetchedArtist
                )
            );
        } else {
            $this->artistInDb->update($this->fetchedArtist);
        }
    }
    private function saveTracks(): void
    {
        // If an artist already exists in the db, find whether
        // there are new tracks, update if true, else
        // insert new tracks
        if ($this->artistInDb) {
            if ($this->artistInDb->tracks()->count() < count($this->fetchedTracks)) {
                $existingTracks = $this->artistInDb
                    ->tracks()
                    ->whereIn('id', array_column($this->fetchedTracks, 'id'))
                    ->pluck('id')
                    ->toArray();
                $newTracks = array_filter(
                    $this->fetchedTracks,
                    fn ($track) => !in_array($track['id'], $existingTracks)
                );

                // Insert new tracks
                if (!empty($newTracks)) {
                    $this->artistInDb->tracks()->insert($newTracks);
                }
            }
        } else {
            Artist::find($this->artistId)->tracks()->insert($this->fetchedTracks);
        }
    }
}
