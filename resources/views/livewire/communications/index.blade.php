<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Comunicaciones</h2>
            <p class="text-sm text-gray-500">Mensajes y recordatorios a la congregación</p>
        </div>
        @can('communication.send')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Comunicación
            </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-3">
        <div class="relative max-w-sm flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por título..."
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Título</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Vía</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Programado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Destinatarios</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($communications as $comm)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.communications.show', $comm) }}" class="font-medium text-gray-800 hover:text-indigo-600 hover:underline">
                                {{ $comm->title }}
                            </a>
                            @if($comm->createdBy)
                                <p class="text-xs text-gray-400">por {{ $comm->createdBy->name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($comm->channel->value) {
                                    'email'    => 'bg-blue-100 text-blue-800',
                                    'sms'      => 'bg-green-100 text-green-800',
                                    'whatsapp' => 'bg-emerald-100 text-emerald-800',
                                } }}">
                                {{ $comm->channel->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                            {{ $comm->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                {{ $comm->recipients_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($comm->status->value) {
                                    'draft'     => 'bg-gray-100 text-gray-600',
                                    'scheduled' => 'bg-yellow-100 text-yellow-800',
                                    'sending'   => 'bg-blue-100 text-blue-800',
                                    'sent'      => 'bg-green-100 text-green-800',
                                    'partial'   => 'bg-orange-100 text-orange-800',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                } }}">
                                {{ $comm->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.communications.show', $comm) }}"
                                   class="text-gray-600 hover:text-gray-800 text-xs font-medium">Ver</a>
                                @can('communication.send')
                                    @if(in_array($comm->status->value, ['draft', 'scheduled']))
                                        <button wire:click="openEdit({{ $comm->id }})"
                                                class="text-gray-600 hover:text-gray-800 text-xs font-medium">Editar</button>
                                    @endif
                                    <button wire:click="duplicate({{ $comm->id }})"
                                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Duplicar</button>
                                    <button wire:click="delete({{ $comm->id }})"
                                            wire:confirm="¿Eliminar esta comunicación?"
                                            class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No hay comunicaciones registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($communications->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $communications->links() }}</div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">{{ $editingId ? 'Editar' : 'Nueva' }} Comunicación</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Título *</label>
                        <input type="text" wire:model="title" placeholder="Ej: Recordatorio — Congreso de Jóvenes"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                        @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Vía de envío *</label>
                            <select wire:model="channel"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('channel') border-red-400 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($channels as $ch)
                                    <option value="{{ $ch->value }}">{{ $ch->label() }}</option>
                                @endforeach
                            </select>
                            @error('channel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Programar envío</label>
                            <input type="datetime-local" wire:model="scheduled_at"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-400 mt-1">Dejar vacío para enviar manualmente.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Mensaje *</label>
                        <textarea wire:model="body" rows="5" placeholder="Escriba el contenido del mensaje..."
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('body') border-red-400 @enderror"></textarea>
                        @error('body') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <p class="text-xs text-gray-400">Los destinatarios se agregan desde la vista de detalle.</p>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
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
