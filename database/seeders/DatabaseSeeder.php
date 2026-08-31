<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create 3 different users
        $users = User::factory(3)->create();

        // 2. Create 20 posts distributed randomly among those 3 users
        Post::factory(20)->create([
            'user_id' => fn() => $users->random()->id,
        ]);
    }
}
