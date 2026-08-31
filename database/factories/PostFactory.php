<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'image_path' => 'https://picsum.photos/640/480?random=' . fake()->unique()->numberBetween(1, 1000),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
