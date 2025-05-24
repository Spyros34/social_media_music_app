<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for your application.
     *
     * @var array<class-string, array<int, string>>
     */
    protected $listen = [
        SocialiteWasCalled::class => [
            // This tells SocialiteProviders to register the Spotify driver
            'SocialiteProviders\\Spotify\\SpotifyExtendSocialite@handle',
        ],
    ];
}