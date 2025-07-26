<?php

use App\Models\User;
use Inertia\Inertia;
use Aerni\Spotify\Facades\Spotify;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SpotifyController;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\EnsureUserIsAuthenticated;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated as MiddlewareRedirectIfAuthenticated;



// 1) Public login page — only guests can see it:
Route::get('/login', [LoginController::class, 'show'])->name('login')
    ->middleware('guest');

Route::middleware('web')->group(function () {
    Route::get('login/spotify', [SpotifyController::class, 'redirectToProvider'])->name('login.spotify');;
    Route::get('login/spotify/callback', [SpotifyController::class, 'handleProviderCallback']);
});

// 3) Home page — only authenticated users:
Route::get('/', [HomeController::class, 'index'])
    ->name('home')
    ->middleware('auth');


Route::middleware('auth')->group(function(){
    Route::post('/posts/{post}/comments', [CommentController::class,'store'])->name('comments.store');
    Route::delete('/comments/{comment}',   [CommentController::class,'destroy'])->name('comments.destroy');
});


Route::get('login/spotify/covers', [SpotifyController::class, 'covers'])
     ->name('login.spotify.covers');