<?php

use App\Models\Post;
use App\Models\User;

test('guests cannot view the posts list', function () {
    $response = $this->get('/posts');

    $response->assertRedirect('/login');
});



test('logged in users can view the posts list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/posts');

    $response->assertStatus(200);
});

test('a logged in user can create a post', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/posts', [
        'title' => 'My Test Post',
        'body' => 'This is a test post body with enough characters.',
    ]);
    $this->assertDatabaseHas('posts', [
        'title' => 'My Test Post',
        'user_id' => $user->id,
    ]);
});

test('a logged in user can update a post', function () {
    $user = User::factory()->create();

    $post = Post::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->put("/posts/{$post->id}", [
        'title' => 'Updated Post Title',
        'body' => 'Updated post body with enough characters.',
    ]);

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Updated Post Title',
        'body' => 'Updated post body with enough characters.',
    ]);
});

test('a user cannot fake another users id when creating a post', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)->post('/posts', [
        'title' => 'Sneaky Post',
        'body' => 'Trying to impersonate someone else.',
        'user_id' => $otherUser->id,   // ← attacker tries to inject someone else's ID
    ]);

    $this->assertDatabaseHas('posts', [
        'title' => 'Sneaky Post',
        'user_id' => $user->id,        // ← should be the REAL logged-in user, not $otherUser
    ]);

    $this->assertDatabaseMissing('posts', [
        'title' => 'Sneaky Post',
        'user_id' => $otherUser->id,   // ← this should NOT exist
    ]);
});

test('a user cannot update another users post', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($intruder)->put("/posts/{$post->id}", [
        'title' => 'Hacked Title',
        'body' => 'This should not be allowed.',
    ]);

    $response->assertForbidden(); // or assertStatus(403)

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
        'title' => 'Hacked Title',
    ]);
});

test('a user cannot delete another users post', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($intruder)->delete("/posts/{$post->id}");

    $response->assertForbidden(); // or assertStatus(403)

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});

test('admins can update and delete any post', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $post = Post::factory()->create();

    $response = $this->actingAs($admin)->put("/posts/{$post->id}", [
        'title' => 'Admin Updated Title',
        'body' => 'Admin Updated Body',
    ]);

    $response->assertRedirect('/posts');
    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Admin Updated Title',
    ]);

    $response = $this->actingAs($admin)->delete("/posts/{$post->id}");

    $response->assertRedirect('/posts');
    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

test('pagination shows correct number of posts', function () {
    $user = User::factory()->create();
    $posts = Post::factory()->count(15)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/posts?page=2');

    $response->assertViewHas('posts', function ($posts) {
        return $posts->count() === 5;
    });
});

test('pagination shows 10 posts on page 1', function () {
    $user = User::factory()->create();
    Post::factory()->count(15)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/posts');

    $response->assertViewHas('posts', fn($posts) => $posts->count() === 10);
});

test('search filters posts by title', function () {
    $user = User::factory()->create();

    Post::factory()->create(['title' => 'Learning Laravel', 'user_id' => $user->id]);
    Post::factory()->create(['title' => 'Cooking recipes', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/posts?search=Laravel');

    $response->assertViewHas(
        'posts',
        fn($posts) =>
        $posts->count() === 1 && $posts->first()->title === 'Learning Laravel'
    );
});

test('searching for the string zero still filters correctly', function () {
    $user = User::factory()->create();

    Post::factory()->create(['title' => '0', 'user_id' => $user->id]);
    Post::factory()->create(['title' => 'Something else', 'user_id' => $user->id]);

    $response = $this . actingAs($user)->get('/posts?search=0');

    $response->assertViewHas(
        'posts',
        fn($posts) =>
        $posts->count() === 1 && $posts->first()->title === '0'
    );
});
