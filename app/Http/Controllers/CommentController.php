<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Events\CommentCreated;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post)
    {
        $validated = $request->validated();

        $comment = $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        CommentCreated::dispatch($comment);
        return redirect("/posts/{$post->id}");
    }

    public function edit(Comment $comment)
    {
        $this->authorize('update', $comment);
        return view('comments.edit', ['comment' => $comment]);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $validated = $request->validated();

        $comment->update([
            'content' => $validated['content'],
        ]);

        return redirect("/posts/{$comment->post_id}");
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return redirect()->back();
    }

    public function apiStore(StoreCommentRequest $request, Post $post)
    {
        $validated = $request->validated();

        $comment = $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
        ]);

        CommentCreated::dispatch($comment);

        return response()->json([
            'message' => 'Comment added successfully!',
            'comment' => $comment->load('user')
        ], 201);
    }

    public function apiUpdate(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validated();
        $comment->update($validated);

        return response()->json([
            'message' => 'Comment updated successfully!',
            'comment' => $comment
        ], 200);
    }

    public function apiDestroy(Request $request, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully!'
        ], 200);
    }
}
