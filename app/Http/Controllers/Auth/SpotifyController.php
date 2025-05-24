<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
// Add this import so the IDE knows about the actual provider class:
use SocialiteProviders\Spotify\Provider as SpotifyProvider;

class SpotifyController extends Controller
{
    /**
     * Redirect the user to Spotify's authorization page.
     */
    public function redirectToProvider()
    {
        /** @var SpotifyProvider $provider */
        $provider = Socialite::driver('spotify');
        
        return $provider
            ->scopes(['user-read-email'])  // runtime-valid, IDE-hinted above
            ->redirect();
    }

    /**
     * Handle Spotify's callback and log the user in.
     */
    public function handleProviderCallback()
    {
        /** @var SpotifyProvider $provider */
        $provider = Socialite::driver('spotify');

        $spotifyUser = $provider
            ->stateless()    // runtime-valid, IDE-hinted above
            ->user();

        $user = User::updateOrCreate(
    ['spotify_id' => $spotifyUser->getId()],
    [
        'name'                  => $spotifyUser->getName(),
        'spotify_token'         => $spotifyUser->token,
        'spotify_refresh_token' => $spotifyUser->refreshToken,
        'spotify_expires_at'    => Carbon::now()->addSeconds($spotifyUser->expiresIn),
    ]
);

        Auth::login($user);

        return redirect()->route('home');
    }
}