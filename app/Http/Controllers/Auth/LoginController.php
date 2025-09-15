<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Aerni\Spotify\Facades\Spotify;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth; // ← import Auth
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    public function show()
    {
        // Example track IDs
        $ids = [
            '0VjIjW4GlUZAMYd2vXMi3b', // Blinding Lights
            '3PfIrDoz19wz7qK7tYeu62', // Levitating
            '4iJyoBOLtHqaGxP12qzhQI'  // Peaches
        ];

        try {
            $response = Spotify::tracks(implode(',', $ids))->get();
            $tracks = $response['tracks'] ?? [];

            $covers = collect($tracks)
    ->filter(fn($t) => isset($t['album']['images']) && is_array($t['album']['images']))
    ->map(fn($t) => $t['album']['images'][1]['url'] ?? $t['album']['images'][0]['url'])
    ->values()
    ->all();
        } catch (\Throwable $e) {
            Log::error('Spotify covers fetch failed: '.$e->getMessage());
            $covers = []; // fallback to empty
        }

        return Inertia::render('Login', [
            'covers'       => $covers,
            'spotifyToken' => optional(Auth::user())->spotify_token,
        ]);
    }

public function logout(Request $request)
{
    // log out the current guard (defaults to 'web' unless you changed it)
    Auth::guard()->logout();

    // fully invalidate the session & rotate CSRF token
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // send them to login
    return redirect()->route('login');
}
}