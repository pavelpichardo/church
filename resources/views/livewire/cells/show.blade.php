<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.cells.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr;
                Volver a Células</a>
            <h2 class="text-xl font-bold text-gray-800 mt-1">{{ $cell->name }}</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1
                {{ match ($cell->status?->value) {
    'active' => 'bg-green-100 text-green-800',
    'inactive' => 'bg-gray-100 text-gray-600',
    'multiplied' => 'bg-blue-100 text-blue-800',
    default => 'bg-gray-100 text-gray-600',
} }}">
                {{ $cell->status?->label() }}
            </span>
        </div>
        @if($cell->status?->value === 'active')
            <button wire:click="openMultiply"
                class="inline-flex items-center cursor-pointer gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Multiplicar
            </button>
        @endif
    </div>

    {{-- Info cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Liderazgo</h3>
            <div class="space-y-2">
                <div>
                    <p class="text-xs text-gray-400">Líder</p>
                    <p class="text-sm font-medium text-gray-800">{{ $cell->leader->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Asistente</p>
                    <p class="text-sm font-medium text-gray-800">{{ $cell->assistant?->full_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Anfitrión</p>
                    <p class="text-sm font-medium text-gray-800">{{ $cell->host?->full_name ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Reunión</h3>
            <div class="space-y-2">
                <div>
                    <p class="text-xs text-gray-400">Día y hora</p>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $cell->meeting_day?->label() ?? '—' }}
                        @if($cell->meeting_time)
                            a las {{ substr($cell->meeting_time, 0, 5) }}
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Dirección</p>
                    <p class="text-sm font-medium text-gray-800">{{ $cell->full_address }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Capacidad</p>
                    <p class="text-sm font-medium text-gray-800">{{ $memberCount }} / {{ $cell->max_capacity }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Genealogía</h3>
            <div class="space-y-2">
                @if($cell->parentCell)
                    <div>
                        <p class="text-xs text-gray-400">Célula madre</p>
                        <a href="{{ route('admin.cells.show', $cell->parentCell) }}"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ $cell->parentCell->name }}</a>
                    </div>
                @endif
                @if($cell->childCells->isNotEmpty())
                    <div>
                        <p class="text-xs text-gray-400">Células hijas</p>
                        @foreach($cell->childCells as $child)
                            <a href="{{ route('admin.cells.show', $child) }}"
                                class="block text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                {{ $child->name }} ({{ $child->leader->full_name }})
                            </a>
                        @endforeach
                    </div>
                @endif
                @if(!$cell->parentCell && $cell->childCells->isEmpty())
                    <p class="text-sm text-gray-400">Sin relaciones de multiplicación.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Members --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Miembros ({{ $memberCount }})</h3>
            @can('cells.update')
                <div class="flex items-center gap-2">
                    <select wire:model="newMemberId"
                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Agregar persona...</option>
                        @foreach($people as $p)
                            @unless($activeMembers->contains('id', $p->id))
                                <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                            @endunless
                        @endforeach
                    </select>
                    <button wire:click="addMember"
                        class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Agregar
                    </button>
                </div>
            @endcan
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                    <th
                        class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">
                        Teléfono</th>
                    <th
                        class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">
                        Desde</th>
                    <th class="px-5 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($activeMembers as $member)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.people.show', $member) }}"
                                class="font-medium text-gray-800 hover:text-indigo-600">{{ $member->full_name }}</a>
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden sm:table-cell">{{ $member->phone ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500 hidden md:table-cell">
                            {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            @can('cells.update')
                                <button wire:click="removeMember({{ $member->id }})"
                                    wire:confirm="¿Remover a {{ $member->full_name }} de esta célula?"
                                    class="text-red-500 hover:text-red-700 text-xs font-medium">Remover</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">No hay miembros en esta célula.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($cell->notes)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Notas</h3>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $cell->notes }}</p>
        </div>
    @endif

    {{-- Multiply Modal --}}
    @if($showMultiplyModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showMultiplyModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h3 class="font-semibold text-gray-800">Multiplicar Célula</h3>
                        @if($cell->assistant)
                            <p class="text-xs text-gray-500 mt-0.5">El asistente actual ({{ $cell->assistant->full_name }}) será
                                el líder de la nueva célula.</p>
                        @else
                            <p class="text-xs text-red-500 mt-0.5">Esta célula no tiene asistente asignado. Asigne uno antes de
                                multiplicar.</p>
                        @endif
                    </div>
                    <button wire:click="$set('showMultiplyModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="multiply" class="px-6 py-5 space-y-4">
                    @if($errors->any())
                        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre de la nueva célula *</label>
                        <input type="text" wire:model="newCellName"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('newCellName') border-red-400 @enderror">
                        @error('newCellName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Liderazgo</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-gray-500 mb-2">Célula madre ({{ $cell->name }})</p>
                                <p class="text-xs text-gray-400 mb-1">Líder: <span
                                        class="text-gray-700">{{ $cell->leader->full_name }}</span> (sin cambio)</p>
                                <label class="block text-xs font-medium text-gray-600 mb-1 mt-2">Nuevo Asistente</label>
                                <select wire:model="parentNewAssistantId"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar...</option>
                                    @foreach($people as $p)
                                        <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="bg-emerald-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-emerald-700 mb-2">Nueva célula</p>
                                <p class="text-xs text-gray-400 mb-1">Líder: <span
                                        class="text-gray-700">{{ $cell->assistant?->full_name ?? '—' }}</span> (asistente
                                    actual)</p>
                                <label class="block text-xs font-medium text-gray-600 mb-1 mt-2">Asistente</label>
                                <select wire:model="childAssistantId"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar...</option>
                                    @foreach($people as $p)
                                        <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                                    @endforeach
                                </select>
                                <label class="block text-xs font-medium text-gray-600 mb-1 mt-2">Anfitrión</label>
                                <select wire:model="newHostId"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar...</option>
                                    @foreach($people as $p)
                                        <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Dirección de la nueva
                            célula</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Dirección *</label>
                                <input type="text" wire:model="newAddressLine1"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('newAddressLine1') border-red-400 @enderror">
                                @error('newAddressLine1') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Dirección línea 2</label>
                                <input type="text" wire:model="newAddressLine2"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Ciudad</label>
                                    <input type="text" wire:model="newCity"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                                    <input type="text" wire:model="newState"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Código Postal</label>
                                    <input type="text" wire:model="newPostalCode"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Día de reunión</label>
                            <select wire:model="newMeetingDay"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Mismo que la célula madre</option>
                                @foreach($days as $d)
                                    <option value="{{ $d->value }}">{{ $d->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Hora</label>
                            <input type="time" wire:model="newMeetingTime"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                            Miembros a transferir *
                        </p>
                        @error('selectedMemberIds') <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                        @if($activeMembers->isEmpty())
                            <p class="text-sm text-gray-400 py-3">No hay miembros activos para transferir.</p>
                        @else
                            <div class="max-h-48 overflow-y-auto space-y-1">
                                @foreach($activeMembers as $member)
                                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" wire:model="selectedMemberIds" value="{{ $member->id }}"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">{{ $member->full_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showMultiplyModal', false)"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" @disabled(!$cell->assistant_id)
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Multiplicar Célula
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>