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

    private function renderProfile(User $profileUser): \Inertia\Response
{
    /** @var \App\Models\User|null $viewer */
    $viewer = Auth::user();

    // avatar for the profile owner
    $profileAvatar = $this->resolveAvatarForUser($profileUser);

    /* ---------------- Author's own posts ---------------- */
    $ownPosts = Post::with('user:id,name,spotify_id')
        ->withCount('comments')
        ->where('user_id', $profileUser->id)
        ->latest()
        ->get();

    // Liked IDs among the author’s own posts (by the VIEWER)
    $likedIdsForOwn = $viewer
        ? $viewer->likedPosts()
            ->whereIn('post_id', $ownPosts->pluck('id'))
            ->pluck('post_id')
            ->all()
        : [];

    $posts = $ownPosts->map(function (Post $p) use ($profileUser, $profileAvatar, $likedIdsForOwn) {
        return [
            'id'        => $p->id,
            'createdAt' => optional($p->created_at)->toIso8601String(),
            'track'     => $p->track ?? [],
            'user'      => [
                'id'         => $profileUser->id,
                'name'       => $profileUser->name,
                'spotify_id' => $profileUser->spotify_id,
                'avatar'     => $profileAvatar,
            ],
            'stats'     => [
                'likes'    => (int) ($p->likes ?? 0),
                'liked'    => in_array($p->id, $likedIdsForOwn, true),
                'comments' => (int) $p->comments_count,
                'reposts'  => 0,
                'saved'    => false,
            ],
        ];
    })->values();

    /* ---------------- Posts this profile user LIKED ---------------- */
    $likedRaw = $profileUser->likedPosts()                 // pivot: user_likes_post
        ->with('user:id,name,spotify_id')
        ->withCount('comments')
        ->orderBy('user_likes_post.created_at', 'desc')
        ->get();

    // Liked IDs among that liked list (by the VIEWER)
    $likedIdsForLikedList = $viewer
        ? $viewer->likedPosts()
            ->whereIn('post_id', $likedRaw->pluck('id'))
            ->pluck('post_id')
            ->all()
        : [];

    $liked = $likedRaw->map(function (Post $p) use ($likedIdsForLikedList) {
        $author       = $p->user;
        $authorAvatar = $this->resolveAvatarForUser($author);

        return [
            'id'        => $p->id,
            'createdAt' => optional($p->created_at)->toIso8601String(),
            'track'     => $p->track ?? [],
            'user'      => [
                'id'         => $author->id,
                'name'       => $author->name,
                'spotify_id' => $author->spotify_id,
                'avatar'     => $authorAvatar,
            ],
            'stats'     => [
                'likes'    => (int) ($p->likes ?? 0),
                // whether the VIEWER has liked this post
                'liked'    => in_array($p->id, $likedIdsForLikedList, true),
                'comments' => (int) $p->comments_count,
                'reposts'  => 0,
                'saved'    => false,
            ],
        ];
    })->values();

    return \Inertia\Inertia::render('Profile/Show', [
        'profile' => [
            'id'         => $profileUser->id,
            'name'       => $profileUser->name,
            'spotify_id' => $profileUser->spotify_id,
            'avatar'     => $profileAvatar,
            'isSelf'     => (bool) ($viewer?->id === $profileUser->id),
        ],
        'posts' => $posts,
        'liked' => $liked,
    ]);
}
    /** Resolve a user's avatar (Spotify cached → fallback to proxied UI Avatars). */
    private function resolveAvatarForUser(User $user): ?string
    {
        if ($user->spotify_id) {
            $url = Cache::remember(
                "spotify:user:{$user->spotify_id}:avatar",
                now()->addHours(12),
                function () use ($user) {
                    try {
                        $profile = Spotify::user($user->spotify_id)->get();
                        return data_get($profile, 'images.0.url');
                    } catch (\Throwable $e) {
                        Log::warning('Spotify profile fetch failed', [
                            'spotify_id' => $user->spotify_id,
                            'msg'        => $e->getMessage(),
                        ]);
                        return null;
                    }
                }
            );
            if ($url) return $url;
        }

        $name = $user->name ?: 'User';
        $ui   = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=1DB954&color=ffffff&bold=true';
        return $this->proxyImg($ui, 128, 128);
    }

    /** Proxy an image via images.weserv.nl to avoid CORS warnings. */
    private function proxyImg(string $url, int $w = 128, int $h = 128): string
    {
        $naked = preg_replace('#^https?://#i', '', $url);
        return 'https://images.weserv.nl/?url=' . urlencode($naked) . "&w={$w}&h={$h}";
    }
}