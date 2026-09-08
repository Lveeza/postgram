<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post)
    {
        $validated = $request->validated();

        $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

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
}
