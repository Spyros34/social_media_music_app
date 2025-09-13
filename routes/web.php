<?php

use App\Models\User;
use Inertia\Inertia;
use Aerni\Spotify\Facades\Spotify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SpotifyController;
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
    // Route::post('/posts/{post}/comments', [CommentController::class,'store'])->name('comments.store');
    // Route::delete('/comments/{comment}',   [CommentController::class,'destroy'])->name('comments.destroy');
});


Route::get('login/spotify/covers', [SpotifyController::class, 'covers'])
     ->name('login.spotify.covers');


Route::middleware('auth')->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('/posts/{post}/like', [PostLikeController::class, 'toggle'])->name('posts.like');
    Route::get('/spotify/search', [SpotifyController::class, 'searchTracks']);
    Route::get('/spotify/track/{id}', [SpotifyController::class, 'track']);
    Route::get('/profile', [ProfileController::class, 'showMe'])->name('profile.me');
    Route::get('/u/{user}', [ProfileController::class, 'show'])->name('profile.show');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
    ->name('posts.destroy');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

