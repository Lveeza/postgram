<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows authenticated user to create a post via api', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['posts:create']);

    $response = $this->postJson('/api/posts', [
        'title' => 'Test Post',
        'body' => 'Test body content',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('posts', ['title' => 'Test Post']);
});

it('rejects post creation without authentication', function () {
    $response = $this->postJson('/api/posts', [
        'title' => 'Test Post',
        'body' => 'Test body content',
    ]);

    $response->assertStatus(401);
});

it('rejects post creation when token lacks posts:create ability', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['posts:update']);

    $response = $this->postJson('/api/posts', [
        'title' => 'Test Post',
        'body' => 'Test body content',
    ]);

    $response->assertStatus(403);
});

it('increments posts_count when a post is created via api', function () {
    $user = User::factory()->create(['posts_count' => 0]);
    Sanctum::actingAs($user, ['posts:create']);

    $this->postJson('/api/posts', [
        'title' => 'Test Post',
        'body' => 'Test body content',
    ]);

    expect($user->fresh()->posts_count)->toBe(1);
});

it('rate limits login attempts after 5 tries', function () {
    $payload = ['email' => 'nobody@example.com', 'password' => 'wrong'];

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', $payload);
    }

    $response = $this->postJson('/api/login', $payload);

    $response->assertStatus(429);
});
