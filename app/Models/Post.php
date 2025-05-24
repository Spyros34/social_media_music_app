<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;
    protected $fillable = ['track', 'likes'];

    public function users()
{
    return $this->belongsToMany(User::class);
}

  /**
     * The users who have liked this post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(
            User::class,
            'user_likes_post',
            'post_id',
            'user_id'
        )
        ->withTimestamps();
    }

    /**
     * Attach a like and increment the counter.
     */
    public function addLikeFrom(User $user): void
    {
        if (! $this->likedByUsers()->where('user_id', $user->id)->exists()) {
            $this->likedByUsers()->attach($user->id);
            $this->increment('likes');
        }
    }

    /**
     * Remove a like and decrement the counter.
     */
    public function removeLikeFrom(User $user): void
    {
        if ($this->likedByUsers()->where('user_id', $user->id)->exists()) {
            $this->likedByUsers()->detach($user->id);
            $this->decrement('likes');
        }
    }
}
