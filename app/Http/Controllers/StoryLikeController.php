<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use Illuminate\Support\Facades\Cache;

class StoryLikeController extends Controller
{
    public function toggle(Request $request, Story $story)
    {
        $user = $request->user();
        $like = $user->storyLikes()->where('story_id', $story->id)->first();

        if ($like) {
            $like->delete();
            $storyLiked = false;
        } else {
            $user->storyLikes()->create(['story_id' => $story->id]);
            $storyLiked = true;
        }
        Cache::tags(['story'])->flush();

        return response()->json([
            'storyLiked' => $storyLiked,
            'storyLikedCount' => $story->likes()->count(),
        ]);
    }
}
