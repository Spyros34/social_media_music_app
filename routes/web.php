<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Aerni\Spotify\Facades\Spotify;
use App\Http\Controllers\Auth\SpotifyController;

Route::get('/', function () {
    return Inertia::render('Home', [
       
    ]);
})-> name('home');

Route::middleware('web')->group(function () {
    Route::get('login/spotify', [SpotifyController::class, 'redirectToProvider']);
    Route::get('login/spotify/callback', [SpotifyController::class, 'handleProviderCallback']);
});