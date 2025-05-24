<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
      protected $fillable = [
        'name',
        'email',
        'password',
        'spotify_id',            // ← add this
        'spotify_token',         // ← and these
        'spotify_refresh_token',
        'spotify_expires_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
     protected $casts = [
        'email_verified_at'   => 'datetime',
        'spotify_expires_at'  => 'datetime',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

     /**
     * The posts this user has liked.
     */
     /**
     * The posts this user has liked.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedPosts()
    {
        return $this->belongsToMany(
            Post::class,
            'user_likes_post', // pivot table
            'user_id',
            'post_id'
        )
        ->withTimestamps();
    }

    /**
     * Check if the user has liked a given post.
     */
    public function hasLiked(Post $post): bool
    {
        return $this->likedPosts()->where('post_id', $post->id)->exists();
    }
}
