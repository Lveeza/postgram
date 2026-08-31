<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center font-sans">

    <div class="w-full max-w-sm">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800" style="font-family: cursive;">Postagram</h1>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm px-6 py-8">
            @if ($errors->any())
            <div class="mb-4 text-sm text-red-500 text-center">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/login" class="space-y-3">
                @csrf
                <input type="email" name="email" placeholder="Email"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">

                <input type="password" name="password" placeholder="Password"
                    class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">

                <button type="submit"
                    class="w-full bg-blue-500 text-white text-sm font-semibold py-2 rounded-md hover:bg-blue-600">
                    Log In
                </button>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm px-6 py-4 mt-3 text-center">
            <p class="text-sm text-gray-600">
                Don't have an account?
                <a href="/register" class="text-blue-500 font-semibold hover:underline">Sign up</a>
            </p>
        </div>
    </div>

</body>

</html>