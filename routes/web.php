<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StatusController;



use App\Models\User;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => $validated['password'],
    ]);

    return redirect('/login')->with('success', 'Registration successful! Please log in.');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/posts');
    }

    return back()->withErrors([
        'email' => 'Invalid credentials.',
    ]);
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
});

Route::get('/status', [StatusController::class, 'index']);

Route::get('/posts', [PostController::class, 'index'])->middleware('auth.check');
Route::post('/posts', [PostController::class, 'store'])->middleware('auth.check');
Route::get('/posts/{post}', [PostController::class, 'show'])->middleware('auth.check');
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->middleware('auth.check');


Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->middleware('auth.check');
Route::put('/posts/{post}', [PostController::class, 'update'])->middleware('auth.check');
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->middleware('auth.check');

Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])->middleware('auth.check');
Route::put('/comments/{comment}', [CommentController::class, 'update'])->middleware('auth.check');
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->middleware('auth.check');

Route::get('/api/posts', [PostController::class, 'apiIndex']);
Route::get('/api/posts/{post}', [PostController::class, 'apiShow']);
