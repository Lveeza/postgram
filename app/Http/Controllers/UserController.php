<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        $authUserId = auth('sanctum')->id();
        $user = User::withIsFollowedByAuth($authUserId)->findOrFail($user->id);
        $user->loadCount(['posts', 'followers', 'following']); // reload counts after re-fetch
        return new UserResource($user);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');

            $user->profile_photo_path = $path;
        }

        if (array_key_exists('bio', $validated)) {
            $user->bio = $validated['bio'];
        }

        $user->save();

        return new UserResource($user);
    }
}
