<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $post->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen font-sans">

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/posts" class="text-2xl font-bold text-gray-800" style="font-family: cursive;">Postagram</a>
            <a href="/posts" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to feed</a>
        </div>
    </nav>

    <div class="max-w-xl mx-auto px-4 py-6">

        <!-- Post card -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm mb-6">

            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-pink-500 to-yellow-400 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-gray-800">{{ $post->user->name }}</span>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    @can('update', $post)
                    <a href="/posts/{{ $post->id }}/edit" class="text-gray-500 hover:text-gray-800">Edit</a>
                    @endcan
                    @can('delete', $post)
                    <form method="POST" action="/posts/{{ $post->id }}" onsubmit="return confirm('Delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                    </form>
                    @endcan
                </div>
            </div>

            @if($post->image_path)
            <img src="{{ asset('storage/' . $post->image_path) }}" alt="{{ $post->title }}"
                class="w-full aspect-square object-cover">
            @endif

            <div class="px-4 py-3">
                <h1 class="font-semibold text-gray-800">{{ $post->title }}</h1>
                <p class="text-gray-600 text-sm mt-1">{{ $post->body }}</p>
            </div>
        </div>

        <!-- Add comment -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6 shadow-sm">
            <form method="POST" action="/posts/{{ $post->id }}/comments">
                @csrf
                <textarea name="content" placeholder="Write a comment..." rows="2"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">{{ old('content') }}</textarea>
                @error('content')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <div class="flex justify-end mt-2">
                    <button type="submit"
                        class="bg-blue-500 text-white text-sm font-semibold px-4 py-1.5 rounded-md hover:bg-blue-600">
                        Post
                    </button>
                </div>
            </form>
        </div>

        <!-- Comments -->
        <div class="space-y-3">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
                Comments ({{ $post->comments->count() }})
            </h2>

            @forelse ($post->comments as $comment)
            <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-purple-400 to-blue-400 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                        <span class="text-sm font-semibold text-gray-800">{{ $comment->user->name }}</span>
                    </div>

                    <div class="flex items-center gap-2 text-xs">
                        @can('update', $comment)
                        <a href="/comments/{{ $comment->id }}/edit" class="text-gray-400 hover:text-gray-700">Edit</a>
                        @endcan
                        @can('delete', $comment)
                        <form method="POST" action="/comments/{{ $comment->id }}" onsubmit="return confirm('Delete this comment?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600">Delete</button>
                        </form>
                        @endcan
                    </div>
                </div>
                <p class="text-gray-600 text-sm mt-1 ml-8">{{ $comment->content }}</p>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">No comments yet. Be the first!</p>
            @endforelse
        </div>

    </div>
</body>

</html>