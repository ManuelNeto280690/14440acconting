<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Forgot password — {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-indigo-50">
    <div class="mx-auto max-w-7xl px-4 py-12">
        <div class="mx-auto max-w-md">
            <div class="text-center mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold">A</span>
                    <span class="text-xl font-semibold text-gray-900">{{ config('app.name') }}</span>
                </a>
                <h1 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">Forgot your password?</h1>
                <p class="mt-2 text-sm text-gray-600">Enter your email and we’ll send you a reset link.</p>
            </div>

            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <ul class="list-disc pl-5 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.password.email') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Send reset link
                </button>

                <p class="text-center text-sm text-gray-600">
                    Remembered your password?
                    <a href="{{ route('tenant.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Back to login</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>