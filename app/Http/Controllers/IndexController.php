<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\YandexMusicParser;
use App\Models\Artist;
use App\Models\Track;

class IndexController extends Controller
{

    public function index()
    {
        return view('index');
    }
    public function store(Request $request)
    {
        $url = $request->post('url');
        $YandexMusicParser = new YandexMusicParser($url);

        return redirect('/');
    }
}
