<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{

    public function index(PostRepositoryInterface $posts)
    {
        $search = request('search');
        $page = request('page', 1);
        $cacheKey = "posts.page.{$page}.search.{$search}";

        $paginatedPosts = Cache::tags(['posts'])->remember($cacheKey, 60, function () use ($posts, $search) {
            return $posts->paginate(10, $search);
        });

        return view('posts.index', ['posts' => $paginatedPosts]);
    }

    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        try {
            DB::transaction(function () use ($validated, $imagePath) {
                Post::create([
                    'title' => $validated['title'],
                    'body' => $validated['body'],
                    'user_id' => auth()->id(),
                    'image_path' => $imagePath,
                ]);

                auth()->user()->increment('posts_count');
            });
        } catch (\Throwable $e) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $e;
        }


        Cache::tags(['posts'])->flush();

        return redirect('/posts');
    }


    public function show(Post $post)
    {
        $post->load('comments.user');
        return view('posts.show', ['post' => $post]);
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        return view('posts.edit', ['post' => $post]);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);
        $validated = $request->validated();

        $post->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        Cache::tags(['posts'])->flush();

        return redirect('/posts');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        DB::transaction(function () use ($post) {
            $post->delete();
            auth()->user()->decrement('posts_count');
        });
        Cache::tags(['posts'])->flush();

        return redirect('/posts');
    }

    // cache api posts

    public function apiIndex(Request $request)
    {
        $search = $request->query('search');
        $page = $request->query('page', 1);

        $userId = auth()->id() ?? 'guest';
        $cacheTag = $search ? md5($search) : 'all';
        $cacheKey = "posts.api.page.{$page}.search.{$cacheTag}.user.{$userId}";

        $posts = Cache::tags(['posts'])->remember($cacheKey, 60, function () use ($search) {
            return Post::with(['user' => function ($query) {
                $query->withCount(['followers', 'following'])
                    ->with(['following' => function ($q) {
                        $q->where('followed_id', auth()->id());
                    }]);
            }])
                ->withCount('likes')
                ->with(['likes' => function ($query) {
                    $query->where('user_id', auth()->id());
                }])
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('body', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(5);
        });

        return PostResource::collection($posts);
    }
    public function apiShow(Post $post)
    {
        $post->load([
            'user' => function ($query) {
                $query->withCount(['followers', 'following'])
                    ->with(['following' => function ($q) {
                        $q->where('followed_id', auth()->id());
                    }]);
            },
            'comments.user',
            'likes' => function ($query) {
                $query->where('user_id', auth()->id());
            }
        ])->loadCount('likes');
        return new PostResource($post);
    }

    public function apiStore(StorePostRequest $request)
    {
        if (! $request->user()->tokenCan('posts:create')) {
            return response()->json(['message' => 'Token does not have permission to create posts.'], 403);
        }

        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = DB::transaction(function () use ($request, $validated, $imagePath) {
            $post = $request->user()->posts()->create([
                'title' => $validated['title'],
                'body' => $validated['body'],
                'image_path' => $imagePath,
            ]);
            $request->user()->increment('posts_count');
            return $post;
        });
        Cache::tags(['posts'])->flush();

        return response()->json(['message' => 'Post created successfully!', 'post' => $post], 201);
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
}
