<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Puertas</h2>
            <p class="text-sm text-gray-500">9 equipos de servicio conectados al sistema de células e IA de routing</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.doors.pending-review') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100 transition-colors">
                🤖 Revisión pendiente
                @if($totals['pending_review'] > 0)
                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold bg-amber-600 text-white">{{ $totals['pending_review'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.doors.ai-log') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-gray-100 border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                Log de IA
            </a>
        </div>
    </div>

    {{-- Stats strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wider text-gray-500">Necesidades abiertas</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totals['open_referrals'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wider text-gray-500">Pendientes de revisión IA</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ $totals['pending_review'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wider text-gray-500">Alertas sin leer</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totals['unread_alerts'] }}</p>
        </div>
    </div>

    {{-- Grid de 9 puertas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($doors as $door)
            <a href="{{ route('admin.doors.show', $door) }}"
               class="block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-gray-300 transition-all overflow-hidden">
                <div class="h-1.5" style="background-color: {{ $door->color ?? '#6b7280' }}"></div>
                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold text-white"
                                  style="background-color: {{ $door->color ?? '#6b7280' }}">
                                {{ $door->order }}
                            </span>
                            <h3 class="font-semibold text-gray-800">{{ $door->name }}</h3>
                        </div>
                        @if($door->open_referrals_count > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                {{ $door->open_referrals_count }} abiertas
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $door->description }}</p>

                    <div class="flex items-center justify-between text-xs">
                        <div class="text-gray-600">
                            @if($door->leaders->isNotEmpty())
                                <span class="font-medium">Líder:</span> {{ $door->leaders->first()->person->full_name }}
                            @else
                                <span class="italic text-gray-400">Sin líder asignado</span>
                            @endif
                        </div>
                        <div class="flex gap-3 text-gray-500">
                            <span title="Voluntarios">👥 {{ $door->active_members_count }}</span>
                            @if($door->unread_alerts_count > 0)
                                <span title="Alertas sin leer" class="text-amber-600">🔔 {{ $door->unread_alerts_count }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif
</div>
