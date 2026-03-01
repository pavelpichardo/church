<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Personas</h2>
            <p class="text-sm text-gray-500">Directorio de visitantes y miembros</p>
        </div>
        @can('people.create')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Persona
            </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Buscar por nombre, email o teléfono..."
                   class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <select wire:model.live="statusFilter"
                class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Todos los estados</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Teléfono</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($people as $person)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            <a href="{{ route('admin.people.show', $person) }}" class="hover:text-indigo-600 hover:underline">{{ $person->full_name }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $person->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $person->phone ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($person->status?->value) {
                                    'active_member'      => 'bg-green-100 text-green-800',
                                    'member'             => 'bg-blue-100 text-blue-800',
                                    'membership_process' => 'bg-yellow-100 text-yellow-800',
                                    'visitor'            => 'bg-gray-100 text-gray-600',
                                    'inactive'           => 'bg-red-100 text-red-700',
                                    default              => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $person->status?->label() ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.people.show', $person) }}"
                                   class="text-gray-600 hover:text-gray-800 text-xs font-medium">Ver</a>
                                @can('people.update')
                                    <button wire:click="openEdit({{ $person->id }})"
                                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Editar</button>
                                @endcan
                                @can('people.delete')
                                    <button wire:click="delete({{ $person->id }})"
                                            wire:confirm="¿Eliminar a {{ $person->full_name }}? Esta acción no se puede deshacer."
                                            class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                            No se encontraron personas.
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

    {{-- Person Form Modal --}}
    <livewire:people.person-form />
</div>
