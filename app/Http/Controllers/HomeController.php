<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Aerni\Spotify\Facades\Spotify;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $albums = Spotify::searchAlbums('jazz')
            ->limit(8)
            ->get();

        $covers = collect($albums['albums']['items'])
            ->map(fn($a) => $a['images'][0]['url'] ?? null)
            ->filter()
            ->values();

        return Inertia::render('Home'
        );
    }
}