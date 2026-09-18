<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoryRequest;
use App\Models\Story;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Resources\StoryResource;
use App\Http\Resources\UserResource;

class StoryController extends Controller
{
    public function store(StoreStoryRequest $request)
    {
        if (! $request->user()->tokenCan('stories:create')) {
            return response()->json(['message' => 'Token does not have permission.'], 403);
        }

        $stories = [];

        if ($request->type === 'text') {
            $stories[] = $request->user()->stories()->create([
                'type' => 'text',
                'content' => $request->content,
                'background_color' => $request->background_color,
            ]);
        } else {
            foreach ($request->file('content', []) as $file) {
                $path = $file->store('stories', 'public');

                $stories[] = $request->user()->stories()->create([
                    'type' => $request->type,
                    'content' => $path,
                    'background_color' => null,
                ]);
            }
        }

        Cache::tags(['story'])->flush();

        return response()->json(['message' => 'Stories created successfully!', 'stories' => $stories], 201);
    }

    public function index(Request $request)
    {
        $followingIds = $request->user()->following()->pluck('users.id');
        $followingIds->push($request->user()->id);

        $stories = Story::whereIn('user_id', $followingIds)
            ->active()
            ->withCount(['likes', 'views'])
            ->with([
                'user',
                'likes' => fn($q) => $q->where('user_id', $request->user()->id),
            ])
            ->latest()
            ->get()
            ->groupBy('user_id');

        $grouped = $stories->map(function ($userStories) {
            return [
                'author' => new UserResource($userStories->first()->user),
                'stories' => StoryResource::collection($userStories),
            ];
        })->values();

        return response()->json($grouped);
    }

    public function destroy(Request $request, Story $story)
    {
        if (! $request->user()->tokenCan('stories:delete')) {
            return response()->json(['message' => 'Token does not have permission.'], 403);
        }

        $this->authorize('delete', $story);

        $story->delete();

        Cache::tags(['story'])->flush();

        return response()->json(['message' => 'Story deleted successfully!'], 200);
    }
}
