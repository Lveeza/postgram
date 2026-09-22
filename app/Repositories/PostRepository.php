<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostRepository implements PostRepositoryInterface
{
    protected Post $model;

    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    public function paginate(int $perPage = 10, ?string $search = null, ?int $authUserId = null)
    {
        return $this->model->newQuery()
            ->with(['user' => function ($query) use ($authUserId) {
                $query->withCount(['posts', 'followers', 'following'])
                    ->withIsFollowedByAuth($authUserId);
            }])
            ->with('media')
            ->withCount('likes', 'comments')
            ->with(['likes' => function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId);
            }])
            ->when(filled($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage);
    }
}
