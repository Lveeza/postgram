<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function show(User $user)
    {
        $user->loadCount(['posts', 'followers', 'following']);
        $authUserId = auth('sanctum')->id();
        $user = User::withIsFollowedByAuth($authUserId)->findOrFail($user->id);
        $user->loadCount(['posts', 'followers', 'following']); // reload counts after re-fetch
        return new UserResource($user);
    }
}
