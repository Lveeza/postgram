<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen font-sans">

    <!-- Top nav -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800" style="font-family: cursive;">Postagram</h1>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-red-500">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-xl mx-auto px-4 py-6">

        <!-- Create post card -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6 shadow-sm">
            <form method="POST" action="/posts" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Title"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                @error('title')
                <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror

                <textarea name="body" placeholder="Write a caption..." rows="2"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">{{ old('body') }}</textarea>
                @error('body')
                <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-between">
                    <input type="file" name="image" accept="image/*"
                        class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-gray-100 file:text-sm file:text-gray-700 hover:file:bg-gray-200">
                    <button type="submit"
                        class="bg-blue-500 text-white text-sm font-semibold px-4 py-1.5 rounded-md hover:bg-blue-600">
                        Share
                    </button>
                </div>
            </form>
        </div>

        <!-- Search -->
        <form class="mb-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts..."
                class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-gray-400">
        </form>

        <!-- Feed -->
        <div class="space-y-6">
            @foreach ($posts as $post)
            <a href="/posts/{{ $post->id }}" class="block bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">

                <!-- Post header -->
                <div class="flex items-center gap-2 px-4 py-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-pink-500 to-yellow-400 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-gray-800">{{ $post->user->name }}</span>
                </div>

                <!-- Image -->
                @if($post->image_path)
                <img src="{{ Str::startsWith($post->image_path, 'http') ? $post->image_path : asset('storage/' . $post->image_path) }}" alt="{{ $post->title }}"
                    class="w-full aspect-square object-cover">
                @endif

                <!-- Content -->
                <div class="px-4 py-3">
                    <h1 class="font-semibold text-gray-800 text-sm">{{ $post->title }}</h1>
                    <p class="text-gray-600 text-sm mt-1">{{ $post->body }}</p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->appends(request()->query())->links() }}
        </div>

    </div>
</body>

</html>