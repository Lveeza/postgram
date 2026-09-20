<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'profile_photo_path'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }


    public function likeCount()
    {
        return $this->likes()->count();
    }


    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id');
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function storyLikes()
    {
        return $this->hasMany(StoryLike::class);
    }

    public function scopeWithIsFollowedByAuth(Builder $query, ?int $authUserId): Builder
    {
        return $query->addSelect([
            'is_following' => \App\Models\Follow::select('id')
                ->whereColumn('followed_id', 'users.id')
                ->where('follower_id', $authUserId ?? 0)
                ->limit(1)
        ])->withCasts(['is_following' => 'boolean']);
    }
}
