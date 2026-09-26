<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Events\CommentCreated;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{

    public function apiIndex(Post $post)
    {
        $comments = $post->comments()->with('user')->orderBy('created_at')->get();
        return CommentResource::collection($comments);
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
            'comment_user' => $comment->load('user'),
            'comment' => new CommentResource($comment)
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
