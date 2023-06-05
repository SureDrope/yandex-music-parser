<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\YandexMusicParser;

class IndexController extends Controller
{

    public function index()
    {
        return view('index');
    }
    public function store(Request $request)
    {
        $url = $request->post('url');
        new YandexMusicParser($url);

        return redirect('/');
    }
}
