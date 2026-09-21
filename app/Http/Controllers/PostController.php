<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostController extends Controller
{

    protected PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function apiIndex(Request $request)
    {
        $search = $request->query('search');
        $cacheCursor = $request->query('cursor', 'first');
        $authId = auth('sanctum')->id();
        $userId = $authId ?? 'guest';

        $cacheTag = $search ? md5($search) : 'all';
        $cacheKey = "posts.api.cursor.{$cacheCursor}.search.{$cacheTag}.user.{$userId}";

        $posts = Cache::tags(['posts'])->remember($cacheKey, 60, function () use ($search, $authId) {
            return $this->postRepository->paginate(10, $search, $authId);
        });

        return PostResource::collection($posts);
    }

    public function apiStore(StorePostRequest $request)
    {
        if (! $request->user()->tokenCan('posts:create')) {
            return response()->json(['message' => 'Token does not have permission to create posts.'], 403);
        }

        $validated = $request->validated();

        $post = DB::transaction(function () use ($request, $validated) {
            $post = $request->user()->posts()->create([
                'title' => $validated['title'],
                'body' => $validated['body'],
            ]);

            foreach ($request->input('media', []) as $index => $item) {
                $type = $item['type'];

                if ($type === 'text') {
                    $content = $item['content'];
                } else {
                    $content = $request->file("media.$index.content")->store('posts', 'public');
                }

                $post->media()->create([
                    'type' => $type,
                    'path' => $content,
                    'order' => $index,
                ]);
            }

            $request->user()->increment('posts_count');
            return $post;
        });

        Cache::tags(['posts'])->flush();

        return response()->json(['message' => 'Post created successfully!', 'post' => $post->load('media')], 201);
    }

    public function apiUpdate(UpdatePostRequest $request, Post $post)
    {
        if (! $request->user()->tokenCan('posts:update')) {
            return response()->json(['message' => 'Token does not have permission.'], 403);
        }
        $this->authorize('update', $post);

        $validated = $request->validated();
        $post->update($validated);

        Cache::tags(['posts'])->flush();
        return response()->json(['message' => 'Post updated successfully!', 'post' => $post], 200);
    }

    public function apiDestroy(Request $request, Post $post)
    {
        if (! $request->user()->tokenCan('posts:delete')) {
            return response()->json(['message' => 'Token does not have permission.'], 403);
        }
        $this->authorize('delete', $post);

        DB::transaction(function () use ($post) {
            $post->delete();
            $post->user->decrement('posts_count');
        });

        Cache::tags(['posts'])->flush();
        return response()->json(['message' => 'Post deleted successfully!'], 200);
    }

    public function apiByUser(User $user): JsonResponse
    {
        $posts = $user->posts()
            ->with(['user' => fn($q) => $q->withCount(['posts', 'followers', 'following'])->withIsFollowedByAuth(auth('sanctum')->id())])
            ->withCount(['likes', 'comments'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate(10);

        return PostResource::collection($posts)->response();
    }
}
