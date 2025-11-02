@extends('layouts.tenant')

@section('title', 'Perfil')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold text-gray-900">Perfil</h1>
            <a href="{{ route('tenant.profile.edit') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">
                Editar Perfil
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-600">Nome</label>
                <div class="mt-1 text-gray-900">{{ $user->name }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Email</label>
                <div class="mt-1 text-gray-900">{{ $user->email }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Criado em</label>
                <div class="mt-1 text-gray-900">{{ $user->created_at?->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Atualizado em</label>
                <div class="mt-1 text-gray-900">{{ $user->updated_at?->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div class="mt-6 border-t pt-4">
            <form action="{{ route('tenant.profile.destroy') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação é irreversível.')">
                @csrf
                @method('DELETE')
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Confirme sua senha</label>
                        <input id="password" name="password" type="password" required
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"/>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium">
                        Excluir Conta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection