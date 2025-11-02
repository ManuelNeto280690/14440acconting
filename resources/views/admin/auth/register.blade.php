<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registro - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Registro de Admin
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Crie sua conta administrativa
            </p>
        </div>

        <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-md" method="POST" action="{{ route('admin.register') }}">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nome Completo
                </label>
                <input id="name" name="name" type="text" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                       value="{{ old('name') }}" placeholder="Seu nome completo">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    E-mail
                </label>
                <input id="email" name="email" type="email" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror"
                       value="{{ old('email') }}" placeholder="admin@example.com">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Senha
                </label>
                <input id="password" name="password" type="password" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-red-500 @enderror"
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirmar Senha
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="••••••••">
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Registrar
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Já tem uma conta? 
                    <a href="{{ route('admin.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Faça login aqui
                    </a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>