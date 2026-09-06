<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;

class PostController extends Controller
{

    public function index(PostRepositoryInterface $posts)
    {
        $search = request('search');
        return view('posts.index', ['posts' => $posts->paginate(10, $search)]);
    }
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }
        Post::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'user_id' => auth()->id(),
            'image_path' => $imagePath,
        ]);

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

        return redirect('/posts');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return redirect('/posts');
    }

    public function apiIndex()
    {
        return PostResource::collection(Post::with('user')->get());
    }

    public function apiShow(Post $post)
    {
        $post->load('comments.user');
        return new PostResource($post);
    }
}
