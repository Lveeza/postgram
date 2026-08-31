<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center font-sans">

    <div class="w-full max-w-sm">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800" style="font-family: cursive;">Postagram</h1>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm px-6 py-8">
            @if (session('success'))
            <div class="mb-4 text-sm text-green-600 text-center">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="/register" class="space-y-3">
                @csrf
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                @error('name')
                <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror

                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                @error('email')
                <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror

                <input type="password" name="password" placeholder="Password"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                @error('password')
                <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror

                <input type="password" name="password_confirmation" placeholder="Confirm Password"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">

                <button type="submit"
                    class="w-full bg-blue-500 text-white text-sm font-semibold py-2 rounded-md hover:bg-blue-600">
                    Sign Up
                </button>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm px-6 py-4 mt-3 text-center">
            <p class="text-sm text-gray-600">
                Already have an account?
                <a href="/login" class="text-blue-500 font-semibold hover:underline">Log in</a>
            </p>
        </div>
    </div>

</body>

</html>