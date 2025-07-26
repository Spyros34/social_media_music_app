<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Aerni\Spotify\Facades\Spotify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
public function index()
{
    $auth = Auth::user();

    // 1) Load posts with authors
    $rawPosts = Post::with(['user:id,name,spotify_id'])
        ->withCount('likedByUsers')
        ->latest()
        ->get();

    // 2) Collect unique Spotify IDs
    $spotifyIds = $rawPosts
        ->pluck('user.spotify_id')
        ->filter()
        ->unique()
        ->values();

    // 3) Resolve avatar URLs (cached)
    $avatarById = [];
    foreach ($spotifyIds as $sid) {
        $avatarById[$sid] = Cache::remember(
            "spotify:user:$sid:avatar",
            now()->addHours(12),
            function () use ($sid) {
                try {
                    $profile = Spotify::user($sid)->get(); // public endpoint
                    return data_get($profile, 'images.0.url');
                } catch (\Throwable $e) {
                    Log::warning('Spotify profile fetch failed', [
                        'spotify_id' => $sid,
                        'msg' => $e->getMessage(),
                    ]);
                    return null;
                }
            }
        );
    }

    // 4) Map response
    $posts = $rawPosts->map(function (Post $post) use ($auth, $avatarById) {
        $t = $post->track ?? [];

        $images = data_get($t, 'album.images', []);
        $cover  = $images[0]['url']
            ?? ($images[1]['url']
            ?? ($images[2]['url']
            ?? data_get($t, 'cover_url')));

        $spotifyId = $post->user->spotify_id;
        $avatar    = $spotifyId ? ($avatarById[$spotifyId] ?? null) : null;

        return [
            'id'        => $post->id,
            'createdAt' => $post->created_at->toIso8601String(),
            'user'      => [
                'id'         => $post->user->id,
                'name'       => $post->user->name,
                'spotify_id' => $spotifyId,
                'avatar'     => $avatar, // <- include Spotify avatar when available
            ],
            'track' => [
                'id'         => data_get($t, 'id'),
                'title'      => data_get($t, 'title', data_get($t, 'name')),
                'artist'     => data_get($t, 'artist', data_get($t, 'artists.0.name')),
                'coverUrl'   => data_get($t, 'coverUrl', $cover),
                'previewUrl' => data_get($t, 'previewUrl', data_get($t, 'preview_url')),
                'externalUrl'=> data_get($t, 'externalUrl', data_get($t, 'external_urls.spotify')),
                'durationMs' => data_get($t, 'durationMs', data_get($t, 'duration_ms')),
            ],
            'stats' => [
                'likes'    => (int) $post->liked_by_users_count,
                'liked'    => $auth ? $post->isLikedBy($auth) : false,
                'reposts'  => 0,
                'saved'    => false,
                'comments' => 0,
            ],
        ];
    })->values();

    return Inertia::render('Home', ['posts' => $posts]);
}
}