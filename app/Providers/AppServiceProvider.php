<?php

namespace App\Providers;

use Inertia\Inertia;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

     protected $listen = [
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\\Spotify\\SpotifyExtendSocialite@handle',
        ],
    ];
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
          Inertia::share([
            'auth' => function () {
                $user = Auth::user();

                return [
                    'user' => $user
                        ? [
                            'id'    => $user->id,
                            'name'  => $user->name,
                            'email' => $user->email,
                        ]
                        : null,
                ];
            },
        ]);
    }
}
