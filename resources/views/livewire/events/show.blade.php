<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.events.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-800">{{ $event->title }}</h2>
            <p class="text-sm text-gray-500">
                {{ $event->starts_at?->format('d/m/Y H:i') }}
                @if($event->location) &mdash; {{ $event->location }} @endif
            </p>
        </div>
        @can('attendance.record')
            <a href="{{ route('admin.events.attendance', $event) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Registrar Asistencia
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT COLUMN: Attendees --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Lista de Asistentes</h3>
                    <span class="text-xs text-gray-500">{{ $totalCount }} total</span>
                </div>

                {{-- Search --}}
                <div class="px-6 py-3 border-b border-gray-100">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Buscar asistente..."
                               class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">#</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Registrado a las</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($attendees as $i => $record)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-400 text-xs">{{ $attendees->firstItem() + $i }}</td>
                                <td class="px-4 py-3">
                                    @if($record->person)
                                        <a href="{{ route('admin.people.show', $record->person) }}"
                                           class="font-medium text-indigo-600 hover:text-indigo-800">
                                            {{ $record->person->full_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($record->person?->status?->value) {
                                            'active_member'      => 'bg-green-100 text-green-800',
                                            'member'             => 'bg-blue-100 text-blue-800',
                                            'membership_process' => 'bg-yellow-100 text-yellow-800',
                                            'visitor'            => 'bg-gray-100 text-gray-600',
                                            'inactive'           => 'bg-red-100 text-red-700',
                                            default              => 'bg-gray-100 text-gray-600',
                                        } }}">
                                        {{ $record->person?->status?->label() ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                                    {{ $record->checked_in_at?->format('H:i') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    {{ $this->search ? 'No se encontraron asistentes.' : 'Aún no hay asistentes registrados.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($attendees->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $attendees->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-6">
            {{-- Información del Evento --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Información del Evento</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Tipo</dt>
                        <dd class="mt-1">
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
                        </dd>
                    </div>
                    @if($event->location)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Lugar</dt>
                            <dd class="text-gray-800">{{ $event->location }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Inicio</dt>
                        <dd class="text-gray-800">{{ $event->starts_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                    @if($event->ends_at)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Fin</dt>
                            <dd class="text-gray-800">{{ $event->ends_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($event->description)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Descripción</dt>
                            <dd class="text-gray-700 whitespace-pre-wrap">{{ $event->description }}</dd>
                        </div>
                    @endif
                    @if($event->createdBy)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Creado por</dt>
                            <dd class="text-gray-800">{{ $event->createdBy->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Resumen --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Resumen</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total asistentes</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $totalCount }}</span>
                    </div>
                    @if($event->ends_at && $event->starts_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Duración</span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ $event->starts_at->diffInMinutes($event->ends_at) }} min
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
