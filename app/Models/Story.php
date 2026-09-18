<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Story extends Model
{

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'background_color',
    ];

    public $timestamps = false;

    public function views()
    {
        return $this->hasMany(StoryView::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }

    public function likes()
    {
        return $this->hasMany(StoryLike::class);
    }
}
