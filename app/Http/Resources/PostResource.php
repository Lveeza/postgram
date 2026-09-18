<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CommentResource;

class PostResource extends JsonResource
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
            'title' => $this->title,
            'body' => $this->body,
            'author' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->diffForHumans(),
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(fn($item) => [
                    'type' => $item->type,
                    'content' => $item->type === 'text'
                        ? $item->path
                        : (str_starts_with($item->path, 'http') ? $item->path : asset('storage/' . $item->path)),
                    'order' => $item->order,
                ]);
            }),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'likes_count' => $this->likes_count,
            'is_liked_by_user' => $this->likes->isNotEmpty(),
        ];
    }
}
