<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.communications.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-800">{{ $communication->title }}</h2>
            <p class="text-sm text-gray-500">
                {{ $communication->channel->label() }}
                @if($communication->scheduled_at)
                    &mdash; Programado: {{ $communication->scheduled_at->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @can('communication.send')
                @if($isEditable)
                    <button wire:click="send"
                            wire:confirm="¿Enviar esta comunicación a {{ $stats['total'] }} destinatarios?"
                            class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors {{ $stats['total'] === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $stats['total'] === 0 ? 'disabled' : '' }}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Enviar Ahora
                    </button>
                @endif
                @if(in_array($communication->status->value, ['scheduled', 'sending']))
                    <button wire:click="cancel"
                            wire:confirm="¿Cancelar esta comunicación?"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancelar
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT: Recipients --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Destinatarios</h3>
                    @if($isEditable)
                        @can('communication.send')
                            <div class="flex items-center gap-3">
                                <button wire:click="openBulkAdd"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Agregar Grupo
                                </button>
                                <button wire:click="openAddPerson"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Agregar Persona
                                </button>
                                @if($stats['total'] > 0)
                                    <button wire:click="clearAllRecipients"
                                            wire:confirm="¿Eliminar todos los destinatarios?"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-red-500 hover:text-red-700">
                                        Limpiar todo
                                    </button>
                                @endif
                            </div>
                        @endcan
                    @endif
                </div>

                {{-- Search & Filter --}}
                <div class="px-6 py-3 border-b border-gray-100 flex items-center gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Buscar destinatario..."
                               class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <select wire:model.live="recipientFilter"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="sent">Enviado</option>
                        <option value="failed">Fallido</option>
                    </select>
                </div>

                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Contacto</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Enviado</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recipients as $r)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    @if($r->person)
                                        <a href="{{ route('admin.people.show', $r->person) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                            {{ $r->person->first_name }} {{ $r->person->last_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs hidden sm:table-cell">
                                    @if($communication->channel->value === 'email')
                                        {{ $r->person?->email ?? '—' }}
                                    @else
                                        {{ $r->person?->phone ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($r->status) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'sent'    => 'bg-green-100 text-green-800',
                                            'failed'  => 'bg-red-100 text-red-700',
                                            default   => 'bg-gray-100 text-gray-600',
                                        } }}">
                                        {{ match($r->status) {
                                            'pending' => 'Pendiente',
                                            'sent'    => 'Enviado',
                                            'failed'  => 'Fallido',
                                            default   => $r->status,
                                        } }}
                                    </span>
                                    @if($r->error_message)
                                        <p class="text-xs text-red-500 mt-0.5 truncate max-w-[200px]" title="{{ $r->error_message }}">{{ $r->error_message }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs hidden md:table-cell">
                                    {{ $r->sent_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($isEditable)
                                        @can('communication.send')
                                            <button wire:click="removeRecipient({{ $r->id }})"
                                                    class="text-red-400 hover:text-red-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    {{ $search || $recipientFilter ? 'No se encontraron destinatarios.' : 'No hay destinatarios. Agregue personas o un grupo.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($recipients->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">{{ $recipients->links() }}</div>
                @endif
            </div>
        </div>

        {{-- RIGHT: Info & Stats --}}
        <div class="space-y-6">
            {{-- Message Preview --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Mensaje</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Vía</dt>
                        <dd>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($communication->channel->value) {
                                    'email'    => 'bg-blue-100 text-blue-800',
                                    'sms'      => 'bg-green-100 text-green-800',
                                    'whatsapp' => 'bg-emerald-100 text-emerald-800',
                                } }}">
                                {{ $communication->channel->label() }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Estado</dt>
                        <dd>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($communication->status->value) {
                                    'draft'     => 'bg-gray-100 text-gray-600',
                                    'scheduled' => 'bg-yellow-100 text-yellow-800',
                                    'sending'   => 'bg-blue-100 text-blue-800',
                                    'sent'      => 'bg-green-100 text-green-800',
                                    'partial'   => 'bg-orange-100 text-orange-800',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                } }}">
                                {{ $communication->status->label() }}
                            </span>
                        </dd>
                    </div>
                    @if($communication->scheduled_at)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Programado para</dt>
                            <dd class="text-gray-800">{{ $communication->scheduled_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($communication->sent_at)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Enviado el</dt>
                            <dd class="text-gray-800">{{ $communication->sent_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($communication->createdBy)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Creado por</dt>
                            <dd class="text-gray-800">{{ $communication->createdBy->name }}</dd>
                        </div>
                    @endif
                </dl>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <dt class="text-xs text-gray-400 font-medium mb-2">Contenido del mensaje</dt>
                    <dd class="text-sm text-gray-700 whitespace-pre-wrap bg-gray-50 rounded-lg p-3">{{ $communication->body }}</dd>
                </div>
            </div>

            {{-- Stats --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Resumen de Envío</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total destinatarios</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</span>
                    </div>
                    @if($stats['sent'] > 0 || $stats['failed'] > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Enviados</span>
                            <span class="text-lg font-bold text-green-600">{{ $stats['sent'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Pendientes</span>
                            <span class="text-lg font-bold text-yellow-600">{{ $stats['pending'] }}</span>
                        </div>
                        @if($stats['failed'] > 0)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Fallidos</span>
                                <span class="text-lg font-bold text-red-600">{{ $stats['failed'] }}</span>
                            </div>
                        @endif
                        @if($stats['total'] > 0)
                            <div class="pt-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all flex">
                                            <div class="bg-green-500 h-full" style="width: {{ round($stats['sent'] / $stats['total'] * 100) }}%"></div>
                                            <div class="bg-red-400 h-full" style="width: {{ round($stats['failed'] / $stats['total'] * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ round($stats['sent'] / $stats['total'] * 100) }}%</span>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Add Person Modal --}}
    @if($showAddModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showAddModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Agregar Destinatario</h3>
                    <button wire:click="$set('showAddModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="addPerson" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar Persona *</label>
                        <input type="text" wire:model.live.debounce.300ms="personSearch"
                               placeholder="Escribe al menos 2 caracteres..."
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('personId') border-red-400 @enderror">
                        @error('personId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

                        @if($searchResults->isNotEmpty())
                            <div class="mt-1 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-48 overflow-y-auto bg-white shadow-lg">
                                @foreach($searchResults as $person)
                                    <button type="button" wire:click="selectPerson({{ $person->id }})"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-indigo-50">
                                        <span class="font-medium">{{ $person->first_name }} {{ $person->last_name }}</span>
                                        <span class="text-xs text-gray-400 ml-2">{{ $person->status?->label() }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showAddModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Bulk Add Modal --}}
    @if($showBulkModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showBulkModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Agregar Grupo de Destinatarios</h3>
                    <button wire:click="$set('showBulkModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Seleccionar grupo</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" wire:model="bulkFilter" value="all" class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-800">Todas las personas</span>
                                    <p class="text-xs text-gray-400">Incluye visitantes, miembros y miembros activos</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" wire:model="bulkFilter" value="member" class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-800">Miembros</span>
                                    <p class="text-xs text-gray-400">Miembros y miembros activos</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" wire:model="bulkFilter" value="active_member" class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-800">Solo miembros activos</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" wire:model="bulkFilter" value="visitor" class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-800">Solo visitantes</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button wire:click="$set('showBulkModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button wire:click="bulkAdd"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Agregar Grupo</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
