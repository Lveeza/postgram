<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FollowController;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::get('/posts', [PostController::class, 'apiIndex']);
Route::get('/posts/{post}', [PostController::class, 'apiShow']);


Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/posts', [PostController::class, 'apiStore']);
    Route::put('/posts/{post}', [PostController::class, 'apiUpdate']);
    Route::delete('/posts/{post}', [PostController::class, 'apiDestroy']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/posts/{post}/comments', [CommentController::class, 'apiStore']);
    Route::put('/comments/{comment}', [CommentController::class, 'apiUpdate']);
    Route::delete('/comments/{comment}', [CommentController::class, 'apiDestroy']);

    Route::post('posts/{post}/likes', [LikeController::class, 'toggle'])
        ->name('posts.likes.toggle');

    Route::post('users/{user}/follow', [FollowController::class, 'toggle'])
        ->name('users.follow.toggle');
});
