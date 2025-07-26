<?php

// app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

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
}