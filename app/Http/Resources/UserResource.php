<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'posts_count' => $this->posts_count ?? null,
            'followers_count' => $this->followers_count ?? null,
            'following_count' => $this->following_count ?? null,
            'is_following' => $this->whenLoaded('following', fn() => $this->following->isNotEmpty()),
        ];
    }
}
