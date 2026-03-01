<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.discipleships.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-gray-800">{{ $discipleship->name }}</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                    {{ match($discipleship->level?->value) {
                        'initial'      => 'bg-green-100 text-green-800',
                        'intermediate' => 'bg-blue-100 text-blue-800',
                        'advanced'     => 'bg-purple-100 text-purple-800',
                        default        => 'bg-gray-100 text-gray-600',
                    } }}">
                    {{ $discipleship->level?->label() ?? '—' }}
                </span>
            </div>
            <p class="text-sm text-gray-500">Detalle del programa</p>
        </div>
        @can('discipleships.assign')
            <a href="{{ route('admin.discipleships.assignments', $discipleship) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Gestionar Asignaciones
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT COLUMN: Assigned People --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Personas Asignadas</h3>
                </div>

                {{-- Filters --}}
                <div class="px-6 py-3 border-b border-gray-100 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Buscar persona..."
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

                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Inicio</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Fin</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($assignments as $a)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    @if($a->person)
                                        <a href="{{ route('admin.people.show', $a->person) }}"
                                           class="font-medium text-indigo-600 hover:text-indigo-800">
                                            {{ $a->person->full_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">
                                    {{ $a->start_date?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                                    {{ $a->end_date?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($a->status?->value) {
                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                            'completed'   => 'bg-green-100 text-green-800',
                                            'cancelled'   => 'bg-red-100 text-red-700',
                                            default       => 'bg-gray-100 text-gray-600',
                                        } }}">
                                        {{ $a->status?->label() ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell truncate max-w-xs">
                                    {{ $a->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    {{ $this->search || $this->statusFilter ? 'No se encontraron personas.' : 'Sin personas asignadas aún.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($assignments->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $assignments->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-6">
            {{-- Información --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Información</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Nombre</dt>
                        <dd class="text-gray-800 font-medium">{{ $discipleship->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Nivel</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($discipleship->level?->value) {
                                    'initial'      => 'bg-green-100 text-green-800',
                                    'intermediate' => 'bg-blue-100 text-blue-800',
                                    'advanced'     => 'bg-purple-100 text-purple-800',
                                    default        => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $discipleship->level?->label() ?? '—' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Duración</dt>
                        <dd class="text-gray-800">{{ $discipleship->duration_weeks ? "{$discipleship->duration_weeks} semanas" : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Líder</dt>
                        <dd class="text-gray-800">{{ $discipleship->leader?->name ?? '—' }}</dd>
                    </div>
                    @if($discipleship->description)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Descripción</dt>
                            <dd class="text-gray-700 whitespace-pre-wrap">{{ $discipleship->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Estadísticas --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Estadísticas</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total asignados</span>
                        <span class="text-lg font-bold text-gray-800">{{ $stats['total'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">En progreso</span>
                        <span class="text-lg font-bold text-yellow-700">{{ $stats['in_progress'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Completados</span>
                        <span class="text-lg font-bold text-green-700">{{ $stats['completed'] }}</span>
                    </div>
                    @if($stats['total'] > 0)
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <span class="text-sm text-gray-500">Tasa de éxito</span>
                            <span class="text-lg font-bold text-indigo-700">
                                {{ round(($stats['completed'] / $stats['total']) * 100) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
