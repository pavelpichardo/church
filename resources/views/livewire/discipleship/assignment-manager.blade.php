<div class="space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.discipleships.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $discipleship->name }}</h2>
            <p class="text-sm text-gray-500">Asignaciones del discipulado</p>
        </div>
        <div class="ml-auto">
            @can('discipleships.assign')
                <button wire:click="$set('showModal', true)"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Asignar Persona
                </button>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Persona</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Inicio</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($assignments as $assignment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $assignment->person->full_name }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">
                            {{ $assignment->start_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($assignment->status?->value) {
                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                    'completed'   => 'bg-green-100 text-green-800',
                                    'cancelled'   => 'bg-red-100 text-red-700',
                                    default       => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $assignment->status?->label() ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('discipleships.complete')
                                @if($assignment->status?->value === 'in_progress')
                                    <button wire:click="complete({{ $assignment->id }})"
                                            wire:confirm="¿Marcar como completado?"
                                            class="text-green-600 hover:text-green-800 text-xs font-medium">
                                        Completar
                                    </button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">
                            No hay asignaciones para este discipulado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($assignments->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $assignments->links() }}</div>
        @endif
    </div>

    {{-- Assign Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Asignar Persona</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit="assign" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Persona *</label>
                        <input type="text" wire:model.live.debounce.300ms="personSearch"
                               placeholder="Buscar por nombre..."
                               autocomplete="off"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('personId') border-red-400 @enderror">
                        @if($people->isNotEmpty() && !$personId)
                            <ul class="mt-1 bg-white border border-gray-200 rounded-lg shadow-sm max-h-40 overflow-y-auto">
                                @foreach($people as $p)
                                    <li wire:click="selectPerson({{ $p->id }}, '{{ addslashes($p->full_name) }}')"
                                        class="px-3 py-2 text-sm hover:bg-indigo-50 cursor-pointer">
                                        {{ $p->full_name }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @error('personId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha Inicio *</label>
                        <input type="date" wire:model="start_date"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Asignar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
