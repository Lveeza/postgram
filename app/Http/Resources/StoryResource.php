<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
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
            'type' => $this->type,
            'content' => $this->type === 'text'
                ? $this->content
                : (str_starts_with($this->content, 'http') ? $this->content : asset('storage/' . $this->content)),
            'background_color' => $this->background_color,
            'created_at' => $this->created_at,

            'likes_count' => (int) ($this->likes_count ?? 0),
            'views_count' => (int) ($this->views_count ?? 0),
            'is_liked_by_user' => $this->likes->isNotEmpty(),
            'user_id' => $this->user_id,
        ];
    }
}
