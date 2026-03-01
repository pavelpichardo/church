<x-layouts.auth>
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Bienvenido</h2>
        <p class="text-sm text-gray-500 mt-1">Primera Iglesia del Nazareno "Ven y Ve"</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                Correo electrónico
            </label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="email"
                   class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                          @error('email') border-red-400 @enderror">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                Contraseña
            </label>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password"
                   class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <div class="flex items-center">
            <input id="remember" type="checkbox" name="remember"
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="remember" class="ml-2 text-sm text-gray-600">Recordarme</label>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white
                       hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       transition-colors">
            Iniciar sesión
        </button>
    </form>
</x-layouts.auth>
