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
        $posts = [
            ['title' => 'Sunset views tonight', 'body' => 'Nothing beats watching the sky change colors after a long day. Grateful for moments like this.'],
            ['title' => 'Coffee and code', 'body' => 'Started the morning debugging for two hours straight. Worth it once it finally worked.'],
            ['title' => 'Weekend hike', 'body' => 'Took the trail up north today. Legs are sore but the view at the top made it all worth it.'],
            ['title' => 'New setup', 'body' => 'Finally organized my desk the way I wanted. Small changes, big difference in focus.'],
            ['title' => 'Late night thoughts', 'body' => 'Sometimes the best ideas come when you least expect them. Writing this down before I forget.'],
            ['title' => 'City lights', 'body' => 'There is something about the city at night that just hits different. Love this view.'],
            ['title' => 'Homemade pasta', 'body' => 'Tried a new recipe today and it actually turned out great. Cooking is therapeutic.'],
            ['title' => 'Rainy day reading', 'body' => 'Perfect weather to stay in with a good book and some tea. No complaints here.'],
            ['title' => 'Gym progress', 'body' => 'Six months in and finally starting to see real progress. Consistency really does pay off.'],
            ['title' => 'Road trip memories', 'body' => 'Threw back to that road trip last summer. Already planning the next one.'],
            ['title' => 'Learning something new', 'body' => 'Picked up a new skill this week. It is humbling to be a beginner again, but exciting too.'],
            ['title' => 'Quiet morning', 'body' => 'Woke up early just to enjoy the silence before the day gets busy. Highly recommend it.'],
            ['title' => 'Beach day', 'body' => 'Salt water and sunshine, exactly what I needed this weekend.'],
            ['title' => 'Project launch', 'body' => 'Finally shipped something I have been working on for weeks. Feels good to see it live.'],
            ['title' => 'Museum visit', 'body' => 'Spent the afternoon wandering through the art exhibit downtown. Inspiring stuff.'],
        ];

        $selected = fake()->randomElement($posts);

        return [
            'title' => $selected['title'],
            'body' => $selected['body'],
            'image_path' => 'https://picsum.photos/640/480?random=' . fake()->unique()->numberBetween(1, 1000),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
