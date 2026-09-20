<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostRepository implements PostRepositoryInterface
{
    public function paginate(int $perPage = 10, ?string $search = null)
    {
        return Post::with('user')
            ->when(filled($search), fn($query) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage);
    }
}
