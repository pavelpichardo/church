<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Células</h2>
            <p class="text-sm text-gray-500">Grupos de crecimiento en hogares</p>
        </div>
        @can('cells.create')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Célula
            </button>
        @endcan
    </div>

    <div class="flex flex-wrap gap-3">
        <div class="relative max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Buscar célula..."
                   class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <select wire:model.live="statusFilter"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Todos los estados</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Célula</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Líder</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Día</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Miembros</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cells as $cell)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.cells.show', $cell) }}" class="font-medium text-gray-800 hover:text-indigo-600 hover:underline">{{ $cell->name }}</a>
                            @if($cell->host)
                                <p class="text-xs text-gray-400">Anfitrión: {{ $cell->host->full_name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-600">
                            {{ $cell->leader->full_name }}
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500">
                            {{ $cell->meeting_day?->label() ?? '—' }}
                            @if($cell->meeting_time)
                                <span class="text-xs text-gray-400">{{ substr($cell->meeting_time, 0, 5) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($cell->status?->value) {
                                    'active'     => 'bg-green-100 text-green-800',
                                    'inactive'   => 'bg-gray-100 text-gray-600',
                                    'multiplied' => 'bg-blue-100 text-blue-800',
                                    default      => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $cell->status?->label() ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                {{ $cell->active_members_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.cells.show', $cell) }}"
                                   class="text-gray-600 hover:text-gray-800 text-xs font-medium">Ver</a>
                                @can('cells.update')
                                    <button wire:click="openEdit({{ $cell->id }})"
                                            class="text-gray-600 hover:text-gray-800 text-xs font-medium">Editar</button>
                                @endcan
                                @can('cells.delete')
                                    <button wire:click="delete({{ $cell->id }})"
                                            wire:confirm="¿Eliminar la célula '{{ $cell->name }}'?"
                                            class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No hay células registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($cells->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $cells->links() }}</div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                 wire:key="cell-modal-{{ $editingId ?? 'create' }}">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">{{ $editingId ? 'Editar' : 'Nueva' }} Célula</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre de la Célula *</label>
                        <input type="text" wire:model="name"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Líder *</label>
                            <select wire:model="leader_id"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('leader_id') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($people as $p)
                                    <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                                @endforeach
                            </select>
                            @error('leader_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Asistente</label>
                            <select wire:model="assistant_id"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('assistant_id') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($people as $p)
                                    <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                                @endforeach
                            </select>
                            @error('assistant_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Anfitrión</label>
                            <select wire:model="host_id"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('host_id') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($people as $p)
                                    <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                                @endforeach
                            </select>
                            @error('host_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Dirección del lugar de reunión</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Dirección *</label>
                                <input type="text" wire:model="address_line1"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('address_line1') border-red-400 @enderror">
                                @error('address_line1') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Dirección línea 2</label>
                                <input type="text" wire:model="address_line2"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Ciudad</label>
                                    <input type="text" wire:model="city"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                                    <input type="text" wire:model="state"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Código Postal</label>
                                    <input type="text" wire:model="postal_code"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Día de reunión</label>
                            <select wire:model="meeting_day"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('meeting_day') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($days as $d)
                                    <option value="{{ $d->value }}">{{ $d->label() }}</option>
                                @endforeach
                            </select>
                            @error('meeting_day') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Hora</label>
                            <input type="time" wire:model="meeting_time"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('meeting_time') border-red-400 @enderror">
                            @error('meeting_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Capacidad máxima</label>
                            <input type="number" wire:model="max_capacity" min="2" max="50"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('max_capacity') border-red-400 @enderror">
                            @error('max_capacity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            {{ $editingId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
