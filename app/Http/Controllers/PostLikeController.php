<?php

// app/Http/Controllers/PostLikeController.php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $user = $request->user();

        if ($post->isLikedBy($user)) {
            $post->removeLikeFrom($user);
            $liked = false;
        } else {
            $post->addLikeFrom($user);
            $liked = true;
        }

        return back()->with([
            'liked' => $liked,
            'likes' => $post->likedByUsers()->count(),
        ]);
    }
}