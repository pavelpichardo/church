<div class="space-y-4">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Proceso de Membresía</h2>
        <p class="text-sm text-gray-500">Seguimiento de etapas de membresía</p>
    </div>

    {{-- Stage Legend --}}
    <div class="flex flex-wrap gap-2">
        @foreach($stages as $stage)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                {{ $stage->order }}. {{ $stage->name }}
            </span>
        @endforeach
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

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Persona</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Etapa Actual</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Avanzar a</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($people as $person)
                    @php
                        $currentStage = $person->membership?->currentStage;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $person->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $person->email ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($currentStage)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $currentStage->name }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">Sin etapa</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($stages as $stage)
                                    @if(!$currentStage || $stage->order > $currentStage->order)
                                        <button wire:click="advance({{ $person->id }}, {{ $stage->id }})"
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                                       bg-gray-100 text-gray-700 hover:bg-indigo-100 hover:text-indigo-800 transition-colors">
                                            {{ $stage->name }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('membership.approve')
                                @if($currentStage?->name === 'Aprobación')
                                    <button wire:click="approve({{ $person->id }})"
                                            wire:confirm="¿Aprobar la membresía de {{ $person->full_name }}?"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold
                                                   bg-green-600 text-white hover:bg-green-700 transition-colors">
                                        Aprobar
                                    </button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">
                            No hay personas en proceso de membresía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($people->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $people->links() }}
            </div>
        @endif
    </div>
</div>
