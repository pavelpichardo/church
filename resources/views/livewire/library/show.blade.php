<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.library.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-gray-800">{{ $studyMaterial->title }}</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                    {{ $studyMaterial->material_type?->label() ?? '—' }}
                </span>
            </div>
            <p class="text-sm text-gray-500">{{ $studyMaterial->author ?? 'Sin autor' }}</p>
        </div>
        @can('library.loan')
            <a href="{{ route('admin.library.loans', $studyMaterial) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Gestionar Préstamos
            </a>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT COLUMN: Loan History --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Historial de Préstamos</h3>
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
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Prestado el</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Vence el</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Devuelto el</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($loans as $loan)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    @if($loan->person)
                                        <a href="{{ route('admin.people.show', $loan->person) }}"
                                           class="font-medium text-indigo-600 hover:text-indigo-800">
                                            {{ $loan->person->full_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">
                                    {{ $loan->assigned_at?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    @if($loan->due_at)
                                        <span class="{{ $loan->status?->value === 'overdue' ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                            {{ $loan->due_at->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden lg:table-cell">
                                    {{ $loan->returned_at?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($loan->status?->value) {
                                            'borrowed' => 'bg-blue-100 text-blue-800',
                                            'returned' => 'bg-green-100 text-green-800',
                                            'overdue'  => 'bg-red-100 text-red-700',
                                            'lost'     => 'bg-gray-200 text-gray-700',
                                            default    => 'bg-gray-100 text-gray-600',
                                        } }}">
                                        {{ $loan->status?->label() ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    {{ $this->search || $this->statusFilter ? 'No se encontraron préstamos.' : 'Sin historial de préstamos.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($loans->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $loans->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-6">
            {{-- Información del Material --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Información del Material</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Título</dt>
                        <dd class="text-gray-800 font-medium">{{ $studyMaterial->title }}</dd>
                    </div>
                    @if($studyMaterial->author)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Autor</dt>
                            <dd class="text-gray-800">{{ $studyMaterial->author }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Tipo</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ $studyMaterial->material_type?->label() ?? '—' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Disponibles</dt>
                        <dd class="mt-1">
                            <span class="text-lg font-bold {{ $studyMaterial->available_quantity > 0 ? 'text-green-700' : 'text-red-600' }}">
                                {{ $studyMaterial->available_quantity }}
                            </span>
                            <span class="text-gray-400 text-sm"> / {{ $studyMaterial->total_quantity }} total</span>
                        </dd>
                    </div>
                    @if($studyMaterial->description)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Descripción</dt>
                            <dd class="text-gray-700 whitespace-pre-wrap">{{ $studyMaterial->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Estadísticas --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Estadísticas</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total préstamos</span>
                        <span class="text-lg font-bold text-gray-800">{{ $stats['total_loans'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Activos</span>
                        <span class="text-lg font-bold text-blue-700">{{ $stats['active'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Vencidos</span>
                        <span class="text-lg font-bold {{ $stats['overdue'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            {{ $stats['overdue'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
