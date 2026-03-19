<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Matrimonios</h2>
            <p class="text-sm text-gray-500">Registro de ceremonias de matrimonio</p>
        </div>
        @can('sacraments.create')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Matrimonio
            </button>
        @endcan
    </div>

    <div class="relative max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Buscar por nombre de cónyuge..."
               class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Novio</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Novia</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Oficiante</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Lugar</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Certificado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($marriages as $m)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $m->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            @if($m->spouse1)
                                <a href="{{ route('admin.people.show', $m->spouse1) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $m->spouse1->first_name }} {{ $m->spouse1->last_name }}
                                </a>
                            @else
                                <span class="text-gray-400">&mdash;</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($m->spouse2)
                                <a href="{{ route('admin.people.show', $m->spouse2) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $m->spouse2->first_name }} {{ $m->spouse2->last_name }}
                                </a>
                            @else
                                <span class="text-gray-400">&mdash;</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $m->officiant?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 hidden lg:table-cell">{{ $m->location ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($m->certificate)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Generado</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.marriages.show', $m) }}"
                                   class="text-gray-600 hover:text-gray-800 text-xs font-medium">Ver</a>
                                @can('sacraments.create')
                                    <button wire:click="openEdit({{ $m->id }})"
                                            class="text-gray-600 hover:text-gray-800 text-xs font-medium">Editar</button>
                                    <button wire:click="delete({{ $m->id }})"
                                            wire:confirm="¿Eliminar este registro de matrimonio?"
                                            class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No hay matrimonios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($marriages->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $marriages->links() }}</div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">{{ $editingId ? 'Editar' : 'Nuevo' }} Matrimonio</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="px-6 py-5 space-y-4">
                    {{-- Date & Time --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha *</label>
                            <input type="date" wire:model="date"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('date') border-red-400 @enderror">
                            @error('date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Hora</label>
                            <input type="time" wire:model="time"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Location --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lugar</label>
                        <input type="text" wire:model="location" placeholder="Ej: Templo principal..."
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Spouse 1 (Novio) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Novio *</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="spouse1Search"
                                   wire:focus="setActiveSearch('spouse1')"
                                   placeholder="Buscar persona..."
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('spouse1Id') border-red-400 @enderror">
                            @error('spouse1Id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            @if($spouse1Results->isNotEmpty())
                                <div class="absolute z-10 mt-1 w-full border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-40 overflow-y-auto bg-white shadow-lg">
                                    @foreach($spouse1Results as $person)
                                        <button type="button" wire:click="selectSpouse1({{ $person->id }})"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-indigo-50">
                                            <span class="font-medium">{{ $person->first_name }} {{ $person->last_name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Spouse 2 (Novia) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Novia *</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="spouse2Search"
                                   wire:focus="setActiveSearch('spouse2')"
                                   placeholder="Buscar persona..."
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('spouse2Id') border-red-400 @enderror">
                            @error('spouse2Id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            @if($spouse2Results->isNotEmpty())
                                <div class="absolute z-10 mt-1 w-full border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-40 overflow-y-auto bg-white shadow-lg">
                                    @foreach($spouse2Results as $person)
                                        <button type="button" wire:click="selectSpouse2({{ $person->id }})"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-indigo-50">
                                            <span class="font-medium">{{ $person->first_name }} {{ $person->last_name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Officiant --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Oficiante</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="officiantSearch"
                                   wire:focus="setActiveSearch('officiant')"
                                   placeholder="Buscar usuario..."
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @if($officiantResults->isNotEmpty())
                                <div class="absolute z-10 mt-1 w-full border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-40 overflow-y-auto bg-white shadow-lg">
                                    @foreach($officiantResults as $user)
                                        <button type="button" wire:click="selectOfficiant({{ $user->id }})"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-indigo-50">
                                            <span class="font-medium">{{ $user->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            {{ $editingId ? 'Actualizar' : 'Registrar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
