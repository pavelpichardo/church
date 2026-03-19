<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.marriages.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-800">
                {{ $marriage->spouse1?->first_name }} {{ $marriage->spouse1?->last_name }}
                &amp;
                {{ $marriage->spouse2?->first_name }} {{ $marriage->spouse2?->last_name }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ $marriage->date->format('d/m/Y') }}
                @if($marriage->location) &mdash; {{ $marriage->location }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @can('sacraments.create')
                @if($marriage->certificate?->file)
                    <a href="{{ Storage::disk($marriage->certificate->file->disk)->url($marriage->certificate->file->path) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Descargar Certificado
                    </a>
                @endif
                <button wire:click="generateCertificate"
                        wire:confirm="¿Generar {{ $marriage->certificate ? 'un nuevo' : 'el' }} certificado de matrimonio en PDF?"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ $marriage->certificate ? 'Regenerar' : 'Generar' }} Certificado
                </button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT: Attendance List --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Lista de Asistencia</h3>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">{{ $totalAttendees }} total</span>
                        @can('attendance.record')
                            <button wire:click="openAttendanceModal"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar
                            </button>
                        @endcan
                    </div>
                </div>

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
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Registrado</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-16"></th>
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
                                            {{ $record->person->first_name }} {{ $record->person->last_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $record->checked_in_at?->format('H:i') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @can('attendance.record')
                                        <button wire:click="removeAttendee({{ $record->id }})"
                                                wire:confirm="¿Quitar a {{ $record->person?->first_name }} de la lista?"
                                                class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    {{ $search ? 'No se encontraron asistentes.' : 'Aún no hay asistentes registrados.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($attendees instanceof \Illuminate\Pagination\LengthAwarePaginator && $attendees->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">{{ $attendees->links() }}</div>
                @endif
            </div>
        </div>

        {{-- RIGHT: Marriage Info --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Detalles del Matrimonio</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Fecha</dt>
                        <dd class="text-gray-800 font-medium">{{ $marriage->date->format('d \d\e F \d\e Y') }}</dd>
                    </div>
                    @if($marriage->location)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Lugar</dt>
                            <dd class="text-gray-800">{{ $marriage->location }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Novio</dt>
                        <dd>
                            @if($marriage->spouse1)
                                <a href="{{ route('admin.people.show', $marriage->spouse1) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $marriage->spouse1->first_name }} {{ $marriage->spouse1->last_name }}
                                </a>
                            @else
                                <span class="text-gray-400">&mdash;</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Novia</dt>
                        <dd>
                            @if($marriage->spouse2)
                                <a href="{{ route('admin.people.show', $marriage->spouse2) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $marriage->spouse2->first_name }} {{ $marriage->spouse2->last_name }}
                                </a>
                            @else
                                <span class="text-gray-400">&mdash;</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Oficiante</dt>
                        <dd class="text-gray-800">{{ $marriage->officiant?->name ?? '—' }}</dd>
                    </div>
                    @if($marriage->notes)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Notas</dt>
                            <dd class="text-gray-700 whitespace-pre-wrap">{{ $marriage->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Certificate Status --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Certificado</h3>
                @if($marriage->certificate)
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Certificado generado</p>
                            <p class="text-xs text-gray-500">{{ $marriage->certificate->issued_at?->format('d/m/Y') }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Sin certificado</p>
                            <p class="text-xs text-gray-400">Use el botón para generar</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Resumen</h3>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Total asistentes</span>
                    <span class="text-2xl font-bold text-gray-800">{{ $totalAttendees }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Attendee Modal --}}
    @if($showAttendanceModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showAttendanceModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Agregar Asistente</h3>
                    <button wire:click="$set('showAttendanceModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="addAttendee" class="px-6 py-5 space-y-4">
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
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showAttendanceModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
