<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Aerni\Spotify\Facades\Spotify;

class ProfileController extends Controller
{
    /** Logged-in user’s profile (/profile) */
    public function showMe(): Response
    {
        return $this->renderProfile(Auth::user());
    }

    /** Another user’s profile (/u/{user}) */
    public function show(User $user): Response
    {
        return $this->renderProfile($user);
    }

    private function renderProfile(User $profileUser): Response
    {
        $viewer = Auth::user();

        // --- Resolve this user's avatar the same way as Home does (Spotify -> cache) ---
        $spotifyId = $profileUser->spotify_id;
        $profileAvatar = $spotifyId
            ? Cache::remember(
                "spotify:user:$spotifyId:avatar",
                now()->addHours(12),
                function () use ($spotifyId) {
                    try {
                        $profile = Spotify::user($spotifyId)->get(); // public endpoint
                        return data_get($profile, 'images.0.url');
                    } catch (\Throwable $e) {
                        Log::warning('Spotify profile fetch failed', [
                            'spotify_id' => $spotifyId,
                            'msg'        => $e->getMessage(),
                        ]);
                        return null;
                    }
                }
            )
            : null;

        // Fallback avatar (proxied to avoid CORS warnings)
        if (!$profileAvatar) {
            $name = $profileUser->name ?: 'User';
            $ui   = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1DB954&color=ffffff&bold=true';
            $profileAvatar = $this->proxyImg($ui, 128, 128);
        }

        // --- Load this user's posts (authors are always the same user on this page) ---
        $posts = Post::with('user:id,name,spotify_id')
            ->withCount('comments')
            ->where('user_id', $profileUser->id)
            ->latest()
            ->get()
            ->map(function (Post $p) use ($viewer, $profileUser, $profileAvatar) {
                $t = $p->track ?? [];

                // keep track payload as-is, PostCard already knows how to read it
                return [
                    'id'        => $p->id,
                    'createdAt' => optional($p->created_at)->toIso8601String(),
                    'track'     => $t,
                    'user'      => [
                        'id'         => $profileUser->id,
                        'name'       => $profileUser->name,
                        'spotify_id' => $profileUser->spotify_id,
                        'avatar'     => $profileAvatar,  // <- same logic as Home
                    ],
                    'stats'     => [
                        'likes'    => (int) ($p->likes ?? 0),
                        'liked'    => $p->isLikedBy($viewer),
                        'comments' => (int) $p->comments_count,
                        'reposts'  => 0,
                        'saved'    => false,
                    ],
                ];
            })
            ->values();

        return Inertia::render('Profile/Show', [
            'profile' => [
                'id'         => $profileUser->id,
                'name'       => $profileUser->name,
                'spotify_id' => $profileUser->spotify_id,
                'avatar'     => $profileAvatar,                 // <- same logic as Home
                'isSelf'     => $viewer?->id === $profileUser->id,
            ],
            'posts' => $posts,
        ]);
    }

    /** Proxy an image through images.weserv.nl (avoids CORS issues with some hosts). */
    private function proxyImg(string $url, int $w = 128, int $h = 128): string
    {
        $naked = preg_replace('#^https?://#i', '', $url);
        return 'https://images.weserv.nl/?url=' . urlencode($naked) . "&w={$w}&h={$h}";
    }
}