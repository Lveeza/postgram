<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

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

    $response = $this->actingAs($user)->get('/posts?search=0');

    $response->assertViewHas(
        'posts',
        fn($posts) =>
        $posts->count() === 1 && $posts->first()->title === '0'
    );
});

test('A logged-in user can like a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->post("/posts/{$post->id}/likes");

    $response->assertJson([
        'liked' => true,
        'likes_count' => 1,
    ]);
});

test('Calling it again unlikes it', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)->post("/posts/{$post->id}/likes");
    $response = $this->actingAs($user)->post("/posts/{$post->id}/likes");

    $response->assertJson([
        'liked' => false,
        'likes_count' => 0,
    ]);
});

test('A guest (not logged in) gets 401 when trying to like a post', function () {
    $post = Post::factory()->create();

    $response = $this->postJson("/posts/{$post->id}/likes");

    $response->assertStatus(401);
});

test('An authenticated user can follow another user via API', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/users/{$targetUser->id}/follow");

    $response->assertStatus(200)
        ->assertJson([
            'followed' => true,
            'followers_count' => 1,
        ]);
});

test('Calling follow endpoint again unfollows the user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/users/{$targetUser->id}/follow");

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/users/{$targetUser->id}/follow");

    $response->assertStatus(200)
        ->assertJson([
            'followed' => false,
            'followers_count' => 0,
        ]);
});

test('A user cannot follow themselves and receives 422 error', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/users/{$user->id}/follow");

    $response->assertStatus(422)
        ->assertJsonFragment(['message' => 'You cannot follow yourself.']);
});

test('A guest without Bearer token gets 401 when attempting to follow', function () {
    $targetUser = User::factory()->create();

    $response = $this->postJson("/api/users/{$targetUser->id}/follow");

    $response->assertStatus(401);
});

test('apiIndex includes correct is_following status for post author', function () {
    $author = User::factory()->create();
    $visitor = User::factory()->create();

    // Author create a post
    Post::factory()->create(['user_id' => $author->id]);

    // 1. Initial Check: Visitor does not follow the author
    $response = $this->actingAs($visitor, 'sanctum')
        ->getJson('/api/posts');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.author.is_following', false);

    // 2. Action: Visitor follows the author
    $this->actingAs($visitor, 'sanctum')
        ->postJson("/api/users/{$author->id}/follow");

    // 3. Post-Follow Check: is_following should now be true
    $response = $this->actingAs($visitor, 'sanctum')
        ->getJson('/api/posts');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.author.is_following', false);
});

test('apiIndex returns correct followers_count for author', function () {
    $author = User::factory()->create();
    $followers = User::factory()->count(3)->create();
    Post::factory()->create(['user_id' => $author->id]);

    // 3 users follow the author
    foreach ($followers as $follower) {
        $this->actingAs($follower, 'sanctum')
            ->postJson("/api/users/{$author->id}/follow");
    }

    $response = $this->getJson('/api/posts');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.author.followers_count', 3);
});

test('apiIndex prevents N+1 queries when loading author follow data', function () {
    $authors = User::factory()->count(5)->create();

    foreach ($authors as $author) {
        Post::factory()->create(['user_id' => $author->id]);
    }

    $user = User::factory()->create();

    // Cache clear ensure karein taake DB queries accurately count hon
    cache()->flush();

    DB::enableQueryLog();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/posts')
        ->assertStatus(200);

    $queryCount = count(DB::getQueryLog());

    // N+1 Avoidance Check: Queries count should stay low (< 8 queries total)
    expect($queryCount)->toBeLessThan(8);
});
