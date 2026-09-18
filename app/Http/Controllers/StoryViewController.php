<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StoryViewController extends Controller
{
    public function store(Request $request, Story $story)
    {
        $userId = $request->user()->id;

        $view = $story->views()->firstOrCreate([
            'user_id' => $userId,
            'story_id' => $story->id,
        ]);

        Cache::tags(['stories'])->flush();

        return response()->json([
            'message' => 'Story view recorded successfully.',
            'views_count' => $story->fresh()->views_count,
        ], 200);
    }
}
