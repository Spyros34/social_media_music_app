<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',   // ← important
        'track',
        'likes',
    ];

    protected $casts = [
        'track' => 'array',   // ← JSON column
    ];

    /** Owner of the post */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** If you really need the post_user pivot, keep it; otherwise remove it to avoid confusion */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class); // uses post_user by convention
    }

    /** Users who liked this post */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_likes_post', 'post_id', 'user_id')
            ->withTimestamps();
    }

    /** Quick helper */
    public function isLikedBy(?User $user): bool
    {
        return $user
            ? $this->likedByUsers()->where('user_id', $user->id)->exists()
            : false;
    }

    /** Attach a like and increment the counter. */
    public function addLikeFrom(User $user): void
    {
        if (! $this->isLikedBy($user)) {
            $this->likedByUsers()->attach($user->id);
            $this->increment('likes');
        }
    }

    /** Remove a like and decrement the counter. */
    public function removeLikeFrom(User $user): void
    {
        if ($this->isLikedBy($user)) {
            $this->likedByUsers()->detach($user->id);
            $this->decrement('likes');
        }
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }
}