<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.doors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Volver a Puertas</a>
            <h2 class="text-xl font-bold text-gray-800 mt-1">Bandeja de revisión — Sugerencias de IA</h2>
            <p class="text-sm text-gray-500">Derivaciones con confianza menor al umbral. Apruebe para activar o rechace para descartar.</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex gap-3">
        <select wire:model.live="doorFilter"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Todas las puertas</option>
            @foreach($doors as $d)
                <option value="{{ $d->code->value }}">{{ $d->order }}. {{ $d->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Cards --}}
    <div class="space-y-3">
        @forelse($referrals as $r)
            <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
                <div class="h-1" style="background-color: {{ $r->door->color ?? '#f59e0b' }}"></div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold text-white"
                                      style="background-color: {{ $r->door->color ?? '#6b7280' }}">
                                    {{ $r->door->order }}. {{ $r->door->name }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                    🤖 IA
                                </span>
                                <span class="text-xs font-mono text-gray-500">conf: {{ number_format($r->ai_confidence ?? 0, 2) }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($r->priority?->value) {
                                        'urgent' => 'bg-red-100 text-red-800',
                                        'high'   => 'bg-amber-100 text-amber-800',
                                        'normal' => 'bg-gray-100 text-gray-700',
                                        'low'    => 'bg-gray-50 text-gray-500',
                                        default  => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ $r->priority?->label() }}
                                </span>
                            </div>

                            <h3 class="font-semibold text-gray-800 mt-2">
                                <a href="{{ route('admin.people.show', $r->person) }}" class="hover:underline">{{ $r->person->full_name }}</a>
                            </h3>
                            <p class="text-xs text-gray-500">Categoría: {{ $r->category ?? '—' }} · creada {{ $r->created_at?->diffForHumans() }}</p>

                            @if($r->ai_reasoning)
                                <p class="text-sm text-gray-700 mt-2 italic border-l-2 border-amber-300 pl-3">
                                    "{{ $r->ai_reasoning }}"
                                </p>
                            @endif
                        </div>

                        <div class="flex gap-2 flex-shrink-0">
                            @can('referrals.review_pending')
                                <button wire:click="approve({{ $r->id }})"
                                        wire:confirm="¿Aprobar esta derivación? Pasará a estar accionable."
                                        class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                                    ✓ Aprobar
                                </button>
                                <button wire:click="reject({{ $r->id }})"
                                        wire:confirm="¿Rechazar esta sugerencia? La derivación será cancelada."
                                        class="inline-flex items-center gap-1 rounded-lg bg-white border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    ✕ Rechazar
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <p class="text-gray-500">No hay derivaciones pendientes de revisión.</p>
                <p class="text-xs text-gray-400 mt-1">Cuando la IA proponga acciones con confianza menor al umbral, aparecerán aquí.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $referrals->links() }}</div>

    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg">{{ session('success') }}</div>
    @endif
</div>
