<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Comment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen font-sans">

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/posts" class="text-2xl font-bold text-gray-800" style="font-family: cursive;">Postagram</a>
            <a href="/posts/{{ $comment->post_id }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Back to post</a>
        </div>
    </nav>

    <div class="max-w-xl mx-auto px-4 py-6">
        <h1 class="text-lg font-semibold text-gray-800 mb-4">Edit Comment</h1>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <form method="POST" action="/comments/{{ $comment->id }}" class="space-y-3">
                @csrf
                @method('PUT')

                <textarea name="content" placeholder="Comment" rows="3"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">{{ old('content', $comment->content) }}</textarea>
                @error('content')
                <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-2 pt-2">
                    <a href="/posts/{{ $comment->post_id }}"
                        class="text-sm text-gray-500 px-4 py-1.5 rounded-md hover:bg-gray-100">Cancel</a>
                    <button type="submit"
                        class="bg-blue-500 text-white text-sm font-semibold px-4 py-1.5 rounded-md hover:bg-blue-600">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>