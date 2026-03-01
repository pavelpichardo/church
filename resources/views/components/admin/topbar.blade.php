<header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-end gap-4 flex-shrink-0">
    <div class="flex items-center gap-3">
        <div class="text-right">
            <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
            @if(auth()->user()->getRoleNames()->isNotEmpty())
                <p class="text-xs text-gray-400">{{ auth()->user()->getRoleNames()->first() }}</p>
            @endif
        </div>

        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-semibold">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors px-3 py-1.5 rounded-lg hover:bg-gray-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Salir
        </button>
    </form>
</header>
