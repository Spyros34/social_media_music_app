<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 
use Illuminate\Support\Facades\Auth;


class CommentController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request, Post $post)
    {
        $request->validate(['body'=>'required|string|max:500']);

        $post->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $request->body,
        ]);

        return back();
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment); // only author can delete
        $comment->delete();
        return back();
    }
}
