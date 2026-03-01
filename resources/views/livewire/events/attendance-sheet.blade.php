<div class="space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.events.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $event->title }}</h2>
            <p class="text-sm text-gray-500">
                {{ $event->starts_at?->format('d/m/Y H:i') }}
                @if($event->location) &mdash; {{ $event->location }} @endif
            </p>
        </div>
        <div class="ml-auto">
            @can('attendance.record')
                <button wire:click="save"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardar Asistencia
                </button>
            @endcan
        </div>
    </div>

    {{-- Summary --}}
    <div class="flex items-center gap-4 text-sm">
        <span class="text-gray-500">Total personas: <strong class="text-gray-800">{{ $people->count() }}</strong></span>
        <span class="text-gray-500">Seleccionados: <strong class="text-indigo-700">{{ count($selected) }}</strong></span>
    </div>

    {{-- Search --}}
    <div class="relative max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Buscar persona..."
               class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    {{-- People List --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="divide-y divide-gray-100">
            @forelse($people as $person)
                @php
                    $alreadyAttended = in_array($person->id, $attended);
                    $isSelected = in_array((string) $person->id, $selected);
                @endphp
                <label class="flex items-center gap-4 px-5 py-3 cursor-pointer hover:bg-gray-50 transition-colors
                              {{ $alreadyAttended ? 'bg-green-50' : '' }}">
                    <input type="checkbox"
                           value="{{ $person->id }}"
                           wire:model="selected"
                           {{ $alreadyAttended ? 'checked disabled' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800">{{ $person->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $person->status?->label() }}</p>
                    </div>
                    @if($alreadyAttended)
                        <span class="text-xs font-medium text-green-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Registrado
                        </span>
                    @endif
                </label>
            @empty
                <p class="px-5 py-10 text-center text-gray-400 text-sm">No hay personas que mostrar.</p>
            @endforelse
        </div>
    </div>

    @if($people->isNotEmpty())
        <div class="flex justify-end">
            @can('attendance.record')
                <button wire:click="save"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardar Asistencia
                </button>
            @endcan
        </div>
    @endif
</div>
