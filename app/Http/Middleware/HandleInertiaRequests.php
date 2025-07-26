<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Illuminate\Http\Request;
use Aerni\Spotify\Facades\Spotify;
use Illuminate\Support\Facades\Cache;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    // public function share(Request $request): array
    // {
    //     return [
    //         ...parent::share($request),
    //         'auth' => [
    //             'user' => $request->user(),
    //         ],
    //     ];
    // }
      public function share(Request $request): array
    {
        $user = $request->user();

        $avatarUrl = null;

        if ($user && $user->spotify_id) {
            $cacheKey = "spotify:avatar:{$user->spotify_id}";

            $avatarUrl = Cache::remember($cacheKey, now()->addHours(6), function () use ($user) {
                try {
                    $profile = Spotify::user($user->spotify_id)->get(); // public profile
                    $images  = $profile['images'] ?? [];
                    return $images[0]['url'] ?? null;
                } catch (\Throwable $e) {
                    return null;
                }
            });
        }

        // fallback avatar (no DB write)
        $fallback = 'https://ui-avatars.com/api/?name='
            . urlencode($user?->name ?? 'Vibe Wave')
            . '&background=1DB954&color=ffffff&bold=true';

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'spotify_id' => $user->spotify_id,
                    // computed avatar field (always present)
                    'avatar'     => $avatarUrl ?: $fallback,
                ] : null,
            ],
        ];
    }
}
