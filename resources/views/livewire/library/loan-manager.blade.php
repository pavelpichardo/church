<div class="space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.library.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">{{ $studyMaterial->title }}</h2>
            <p class="text-sm text-gray-500">
                Disponibles:
                <span class="font-semibold {{ $studyMaterial->available_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $studyMaterial->available_quantity }}
                </span>
                / {{ $studyMaterial->total_quantity }}
            </p>
        </div>
        <div class="ml-auto">
            @can('library.loan')
                <button wire:click="$set('showModal', true)"
                        @if($studyMaterial->available_quantity < 1) disabled @endif
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Préstamo
                </button>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Persona</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Prestado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Vence</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($loans as $loan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $loan->person->full_name }}</td>
                        <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">
                            {{ $loan->assigned_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($loan->due_at)
                                <span class="{{ $loan->due_at->isPast() && !in_array($loan->status?->value, ['returned']) ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                    {{ $loan->due_at->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($loan->status?->value) {
                                    'borrowed' => 'bg-blue-100 text-blue-800',
                                    'overdue'  => 'bg-red-100 text-red-700',
                                    'returned' => 'bg-green-100 text-green-800',
                                    default    => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $loan->status?->label() ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('library.return')
                                @if(in_array($loan->status?->value, ['borrowed', 'overdue']))
                                    <button wire:click="returnLoan({{ $loan->id }})"
                                            wire:confirm="¿Registrar devolución de {{ $loan->person->full_name }}?"
                                            class="text-green-600 hover:text-green-800 text-xs font-medium">
                                        Devolver
                                    </button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                            No hay préstamos registrados para este material.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($loans->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $loans->links() }}</div>
        @endif
    </div>

    {{-- Loan Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Nuevo Préstamo</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit="loan" class="px-6 py-5 space-y-4">
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
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de Devolución</label>
                        <input type="date" wire:model="due_at"
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
                            Prestar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
