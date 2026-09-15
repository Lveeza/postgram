<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FollowController extends Controller
{
    public function toggle(Request $request, User $user)
    {
        $authUser = $request->user();

        if ($authUser->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself.'], 422);
        }

        $follow = $authUser->following()->where('followed_id', $user->id)->first();

        if ($follow) {
            $follow->delete();
            $followed = false;
        } else {
            $authUser->following()->attach($user->id);
            $followed = true;
        }

        Cache::tags(['posts'])->flush();

        return response()->json([
            'followed' => $followed,
            'followers_count' => $user->followers()->count(),
        ]);
    }
}
