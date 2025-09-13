<?php

// app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StorePostRequest;

class PostController extends Controller
{

    public function store(StorePostRequest $request): RedirectResponse
    {
        Post::create([
            'user_id' => $request->user()->id,
            'track'   => $request->validated('track'),
            'likes'   => 0,
        ]);

        return redirect()->route('home')->with('success', 'Post created!');
    }
    public function destroy(Post $post): RedirectResponse
    {
        // DO NOT call ->check() on the user.
        // DO NOT call ->id() as a method on the user.
        // USE THE FACADE:
        $userId  = (int) Auth::id();                 // null => 0 when cast
        $ownerId = (int) $post->getAttribute('user_id');

        if ($userId === 0 || $userId !== $ownerId) {
            abort(403, 'Not authorized to delete this post.');
        }

        $post->delete();
        return back()->with('success', 'Post deleted.');
    }
    
}