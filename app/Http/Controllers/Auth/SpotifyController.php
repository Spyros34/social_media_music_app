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
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Spotify\Provider as SpotifyProvider;


class SpotifyController extends Controller
{
    /**
     * Redirect the user to Spotify's authorization page.
     */
 public function redirectToProvider()
{
    $callback = route('login.spotify.callback');

    /** @var \SocialiteProviders\Spotify\Provider|\Laravel\Socialite\Two\AbstractProvider $provider */
    $provider = Socialite::driver('spotify');

    return $provider
        ->redirectUrl($callback)     // set exact callback URL
        ->scopes(['user-read-email'])// set scopes (method exists on AbstractProvider)
        ->redirect();
}

   public function searchTracks(Request $request): JsonResponse
{
    $q = trim((string) $request->query('q', ''));
    if ($q === '') {
        return response()->json(['items' => []]);
    }

    try {
        $res = Spotify::searchTracks($q)->limit(10)->get();

        $items = collect($res['tracks']['items'] ?? [])->map(function ($t) {
            // prefer the largest image if available
            $images = $t['album']['images'] ?? [];
            $cover = $images[0]['url'] ?? ($images[1]['url'] ?? ($images[2]['url'] ?? null));

            return [
                'id'         => $t['id'] ?? null,
                'title'      => $t['name'] ?? '',
                'artist'     => $t['artists'][0]['name'] ?? '',
                'coverUrl'   => $cover,   // <- IMPORTANT
                'previewUrl' => $t['preview_url'] ?? null,
                'externalUrl'=> $t['external_urls']['spotify'] ?? null,
                'durationMs' => $t['duration_ms'] ?? null,
            ];
        })->values();

        return response()->json(['items' => $items], 200);
    } catch (\Throwable $e) {
        Log::error('Spotify search failed', ['q' => $q, 'msg' => $e->getMessage()]);
        return response()->json(['items' => []], 200);
    }
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

     /**
     * Return normalized details for a Spotify track id.
     * Uses client-credentials token via aerni/laravel-spotify.
     */
   public function track(string $id): JsonResponse
{
    $cacheKey = "spotify:track:$id";

    try {
        $data = Cache::remember($cacheKey, now()->addHours(6), function () use ($id) {
            $track = Spotify::track($id)->get(); // may throw

            return [
                'id'         => $track['id'],
                'title'      => $track['name'],
                'artist'     => $track['artists'][0]['name'] ?? '',
                'coverUrl'   => $track['album']['images'][0]['url'] ?? '',
                'previewUrl' => $track['preview_url'] ?? null,
                'externalUrl'=> $track['external_urls']['spotify'] ?? null,
                'durationMs' => $track['duration_ms'] ?? null,
            ];
        });

        return response()->json($data, 200);
    } catch (\Throwable $e) {
        Log::error('Spotify track fetch failed', [
            'track_id' => $id,
            'message'  => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Track fetch failed.',
        ], 502);
    }
}
}