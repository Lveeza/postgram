<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryLikeController;
use App\Http\Controllers\StoryViewController;
use App\Http\Controllers\UserController;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::get('/posts', [PostController::class, 'apiIndex']);
Route::get('/posts/{post}', [PostController::class, 'apiShow']);
Route::get('users/{user}', [UserController::class, 'show']);
Route::get('/users/{user}/posts', [PostController::class, 'apiByUser']);



Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [UserController::class, 'update']);

    Route::post('/posts', [PostController::class, 'apiStore']);
    Route::put('/posts/{post}', [PostController::class, 'apiUpdate']);
    Route::delete('/posts/{post}', [PostController::class, 'apiDestroy']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/posts/{post}/comments', [CommentController::class, 'apiIndex']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'apiStore']);
    Route::put('/comments/{comment}', [CommentController::class, 'apiUpdate']);
    Route::delete('/comments/{comment}', [CommentController::class, 'apiDestroy']);

    Route::post('posts/{post}/likes', [LikeController::class, 'toggle'])
        ->name('posts.likes.toggle');

    Route::post('users/{user}/follow', [FollowController::class, 'toggle'])
        ->name('users.follow.toggle');

    Route::post('stories', [StoryController::class, 'store']);
    Route::get('stories', [StoryController::class, 'index']);
    Route::delete('stories/{story}', [StoryController::class, 'destroy']);
    Route::post('stories/{story}/likes', [StoryLikeController::class, 'toggle']);
    Route::post('/stories/{story}/views', [StoryViewController::class, 'store'])->middleware('auth:sanctum');
});
