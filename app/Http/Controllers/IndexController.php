<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\YandexMusicParser;

class IndexController extends Controller
{
    public function __construct()
    {
    }
    public function index()
    {
        return view('index');
    }
    public function store(Request $request)
    {
        $url = $request->post('url');
        $YandexMusicParser = new YandexMusicParser($url);
        // $artist_id = $YandexMusicParser->getArtistIdFromUrl($url);
        $YandexMusicParser->fetchJsonData();
    }
}
