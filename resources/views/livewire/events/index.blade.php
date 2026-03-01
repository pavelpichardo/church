<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Eventos</h2>
            <p class="text-sm text-gray-500">Cultos, clases y eventos especiales</p>
        </div>
        @can('events.create')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Evento
            </button>
        @endcan
    </div>

    <div class="relative max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Buscar evento..."
               class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Evento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Fecha</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Asistentes</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($events as $event)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $event->title }}</p>
                            @if($event->location)
                                <p class="text-xs text-gray-400">{{ $event->location }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($event->event_type?->value) {
                                    'service'       => 'bg-blue-100 text-blue-800',
                                    'class'         => 'bg-green-100 text-green-800',
                                    'discipleship'  => 'bg-purple-100 text-purple-800',
                                    'special_event' => 'bg-yellow-100 text-yellow-800',
                                    'congress'      => 'bg-red-100 text-red-700',
                                    default         => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $event->event_type?->label() ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                            {{ $event->starts_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                {{ $event->attendance_records_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.events.attendance', $event) }}"
                                   class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Asistencia</a>
                                @can('events.update')
                                    <button wire:click="openEdit({{ $event->id }})"
                                            class="text-gray-600 hover:text-gray-800 text-xs font-medium">Editar</button>
                                @endcan
                                @can('events.delete')
                                    <button wire:click="delete({{ $event->id }})"
                                            wire:confirm="¿Eliminar '{{ $event->title }}'?"
                                            class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No hay eventos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($events->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $events->links() }}</div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">{{ $editingId ? 'Editar' : 'Nuevo' }} Evento</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Título *</label>
                        <input type="text" wire:model="title"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                        @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo *</label>
                        <select wire:model="event_type"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('event_type') border-red-400 @enderror">
                            <option value="">Seleccionar...</option>
                            @foreach($types as $t)
                                <option value="{{ $t->value }}">{{ $t->label() }}</option>
                            @endforeach
                        </select>
                        @error('event_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Inicio *</label>
                            <input type="datetime-local" wire:model="starts_at"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('starts_at') border-red-400 @enderror">
                            @error('starts_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Fin</label>
                            <input type="datetime-local" wire:model="ends_at"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lugar</label>
                        <input type="text" wire:model="location"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                        <textarea wire:model="description" rows="2"
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
