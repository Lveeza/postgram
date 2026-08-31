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
            'author' => $this->user->name,
            'created_at' => $this->created_at->diffForHumans(),
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
