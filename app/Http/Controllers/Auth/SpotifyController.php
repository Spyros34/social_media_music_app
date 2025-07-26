<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Aerni\Spotify\Facades\Spotify;
// Add this import so the IDE knows about the actual provider class:
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
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
    public function handleProviderCallback(Request $request)
    {
        // 1) Did the user cancel?
        if ($request->get('error') === 'access_denied') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Spotify access was canceled.');
        }

        try {
            $spotifyUser = Socialite::driver('spotify')
                ->user();  // no stateless(), state is tracked
        } catch (\Exception $e) {
            Log::warning('Spotify OAuth callback error: '.$e->getMessage());

            return redirect()
                ->route('login')
                ->with('error', 'Failed to authenticate with Spotify.');
        }

        // 2) Build only the Spotify fields:
        $data = [
            'name'                  => $spotifyUser->getName() ?? $spotifyUser->getNickname(),
            'spotify_token'         => $spotifyUser->token,
            'spotify_refresh_token' => $spotifyUser->refreshToken,
            'spotify_expires_at'    => Carbon::now()->addSeconds($spotifyUser->expiresIn),
        ];

        // If Spotify gave us an email, include it
        if ($email = $spotifyUser->getEmail()) {
            $data['email'] = $email;
        }

        // 3) Upsert by spotify_id
        $user = User::updateOrCreate(
            ['spotify_id' => $spotifyUser->getId()],
            $data
        );

        // 4) Log them in
        Auth::login($user, true);

        return redirect()->route('home');
    }

 /**
     * Return an array of album-cover URLs for the login carousel.
     */
    public function covers(): JsonResponse
    {
        // example track IDs
        $ids = [
            '0VjIjW4GlUZAMYd2vXMi3b', // Blinding Lights
            '3PfIrDoz19wz7qK7tYeu62', // Levitating
            '4iJyoBOLtHqaGxP12qzhQI'  // Peaches
        ];

        try {
            // fetch track data via aerni/laravel-spotify
            $tracks = Spotify::tracks(implode(',', $ids))->get();

            $covers = collect($tracks)
                ->map(fn($t) => $t['album']['images'][1]['url']
                               ?? $t['album']['images'][0]['url'])
                ->values()
                ->all();

            return response()->json($covers);
        } catch (\Throwable $e) {
            // log the full exception for debugging
            Log::error('Spotify covers error: '.$e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);

            // return empty list (or you could return a default set of URLs)
            return response()->json([], 200);
        }
    }
}