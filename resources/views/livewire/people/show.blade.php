<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.people.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-gray-800">{{ $person->full_name }}</h2>
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
            </div>
            <p class="text-sm text-gray-500">Perfil de persona</p>
        </div>
        @can('people.update')
            <button wire:click="openEdit"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </button>
        @endcan
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT COLUMN --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Información Personal --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Información Personal</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Nombre</dt>
                        <dd class="text-gray-800 font-medium">{{ $person->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Email</dt>
                        <dd class="text-gray-800">{{ $person->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Teléfono</dt>
                        <dd class="text-gray-800">{{ $person->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Género</dt>
                        <dd class="text-gray-800">{{ $person->gender?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Estado Civil</dt>
                        <dd class="text-gray-800">{{ $person->marital_status?->label() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Fecha de Nacimiento</dt>
                        <dd class="text-gray-800">{{ $person->birth_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Primera Visita</dt>
                        <dd class="text-gray-800">{{ $person->first_visit_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">¿Cómo nos encontró?</dt>
                        <dd class="text-gray-800">{{ $person->how_found_us?->label() ?? '—' }}</dd>
                    </div>
                    @if($person->city || $person->state)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-400 font-medium">Ciudad / Estado</dt>
                            <dd class="text-gray-800">{{ collect([$person->city, $person->state])->filter()->join(', ') }}</dd>
                        </div>
                    @endif
                    @if($person->address_line1)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-400 font-medium">Dirección</dt>
                            <dd class="text-gray-800">{{ $person->address_line1 }}{{ $person->address_line2 ? ', ' . $person->address_line2 : '' }}</dd>
                        </div>
                    @endif
                </dl>
                @if($person->notes_pastoral)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-xs text-gray-400 font-medium mb-1">Notas Pastorales</dt>
                        <dd class="text-sm text-gray-700 whitespace-pre-wrap">{{ $person->notes_pastoral }}</dd>
                    </div>
                @endif
            </div>

            {{-- Membresía --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Membresía</h3>
                @if($person->membership)
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800">
                                {{ $person->membership->currentStage?->name ?? 'Sin etapa' }}
                            </span>
                        </div>

                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <dt class="text-xs text-gray-400 font-medium">Clase tomada</dt>
                                <dd class="text-gray-800">{{ $person->membership->class_taken_at?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400 font-medium">Documento firmado</dt>
                                <dd class="text-gray-800">{{ $person->membership->document_signed_at?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400 font-medium">Aprobado por pastor</dt>
                                <dd class="text-gray-800">{{ $person->membership->pastor_approved_at?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                        </dl>

                        {{-- Advance stage buttons --}}
                        @can('membership.advance')
                            @php
                                $currentOrder = $person->membership->currentStage?->order ?? 0;
                            @endphp
                            @if($stages->where('order', '>', $currentOrder)->count() > 0)
                                <div class="pt-2">
                                    <p class="text-xs text-gray-500 mb-2">Avanzar a etapa:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($stages->where('order', '>', $currentOrder) as $stage)
                                            <button wire:click="advance({{ $stage->id }})"
                                                    wire:confirm="¿Avanzar a la etapa '{{ $stage->name }}'?"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg border border-indigo-300 text-xs font-medium text-indigo-700 hover:bg-indigo-50 transition-colors">
                                                {{ $stage->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endcan

                        @can('membership.approve')
                            @if($person->membership->currentStage?->name === 'Aprobación')
                                <div>
                                    <button wire:click="approve"
                                            wire:confirm="¿Aprobar la membresía de {{ $person->full_name }}?"
                                            class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Aprobar Membresía
                                    </button>
                                </div>
                            @endif
                        @endcan

                        {{-- Stage History --}}
                        @if($person->membershipHistory->count() > 0)
                            <div class="pt-2 border-t border-gray-100">
                                <p class="text-xs text-gray-500 mb-3">Historial de etapas</p>
                                <div class="space-y-2">
                                    @foreach($person->membershipHistory->sortByDesc('changed_at') as $h)
                                        <div class="flex items-center gap-2 text-xs text-gray-600">
                                            <span class="text-gray-400">{{ $h->changed_at?->format('d/m/Y') }}</span>
                                            <span class="text-gray-300">·</span>
                                            <span>{{ $h->fromStage?->name ?? 'Inicio' }}</span>
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <span class="font-medium text-gray-800">{{ $h->toStage?->name }}</span>
                                            @if($h->changedBy)
                                                <span class="text-gray-400">por {{ $h->changedBy->name }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-400">Sin proceso de membresía registrado.</p>
                    @can('membership.advance')
                        @if($stages->count() > 0)
                            <div class="mt-3">
                                <p class="text-xs text-gray-500 mb-2">Iniciar proceso en etapa:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($stages->take(1) as $stage)
                                        <button wire:click="advance({{ $stage->id }})"
                                                wire:confirm="¿Iniciar el proceso de membresía en '{{ $stage->name }}'?"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg border border-indigo-300 text-xs font-medium text-indigo-700 hover:bg-indigo-50 transition-colors">
                                            Iniciar en: {{ $stage->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endcan
                @endif
            </div>

            {{-- Discipulados --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Discipulados</h3>
                </div>
                @if($person->discipleshipAssignments->count() > 0)
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Programa</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Inicio</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($person->discipleshipAssignments as $a)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.discipleships.show', $a->discipleship) }}"
                                           class="font-medium text-indigo-600 hover:text-indigo-800">
                                            {{ $a->discipleship?->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">
                                        {{ $a->start_date?->format('d/m/Y') ?? '—' }}
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="px-6 py-6 text-sm text-gray-400">Sin discipulados asignados.</p>
                @endif
            </div>

            {{-- Asistencia Reciente --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Asistencia Reciente</h3>
                </div>
                @if($recentAttendance->count() > 0)
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Evento</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tipo</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentAttendance as $r)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3">
                                        @if($r->event)
                                            <a href="{{ route('admin.events.show', $r->event) }}"
                                               class="font-medium text-indigo-600 hover:text-indigo-800">
                                                {{ $r->event->title }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        <span class="text-gray-500">{{ $r->event?->event_type?->label() ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">
                                        {{ $r->checked_in_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="px-6 py-6 text-sm text-gray-400">Sin registros de asistencia.</p>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-6">

            {{-- Préstamos Activos --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Préstamos Activos</h3>
                @if($activeLoans->count() > 0)
                    <div class="space-y-3">
                        @foreach($activeLoans as $loan)
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('admin.library.show', $loan->studyMaterial) }}"
                                       class="text-sm font-medium text-indigo-600 hover:text-indigo-800 block truncate">
                                        {{ $loan->studyMaterial?->title ?? '—' }}
                                    </a>
                                    <p class="text-xs mt-0.5 {{ $loan->status?->value === 'overdue' ? 'text-red-500' : 'text-gray-400' }}">
                                        Vence: {{ $loan->due_at?->format('d/m/Y') ?? '—' }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                                    {{ $loan->status?->value === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $loan->status?->label() ?? '—' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400">Sin préstamos activos.</p>
                @endif
            </div>

            {{-- Historial de Préstamos --}}
            @if($pastLoans->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Historial de Préstamos</h3>
                    <div class="space-y-3">
                        @foreach($pastLoans as $loan)
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('admin.library.show', $loan->studyMaterial) }}"
                                       class="text-sm font-medium text-gray-700 hover:text-indigo-600 block truncate">
                                        {{ $loan->studyMaterial?->title ?? '—' }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Devuelto: {{ $loan->returned_at?->format('d/m/Y') ?? '—' }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                                    {{ match($loan->status?->value) {
                                        'returned' => 'bg-green-100 text-green-800',
                                        'lost'     => 'bg-red-100 text-red-700',
                                        default    => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ $loan->status?->label() ?? '—' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Person Form Modal (always present) --}}
    <livewire:people.person-form />
</div>
