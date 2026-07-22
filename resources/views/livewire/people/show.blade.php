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

            {{-- Puertas --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Puertas</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Equipos donde sirve y derivaciones recibidas</p>
                </div>

                {{-- Actualmente en --}}
                @if($currentMemberships->isNotEmpty() || $openReferrals->isNotEmpty())
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Actualmente</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($currentMemberships as $m)
                                <a href="{{ route('admin.doors.show', $m->door) }}"
                                   class="inline-flex items-center gap-1.5 rounded-full pl-1.5 pr-3 py-1 text-xs font-medium border border-gray-200 hover:bg-white transition-colors">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $m->door->color ?? '#6b7280' }}"></span>
                                    {{ $m->door->name }}
                                    <span class="text-gray-400">· {{ $m->role?->label() }}</span>
                                </a>
                            @endforeach
                            @foreach($openReferrals as $r)
                                <a href="{{ route('admin.doors.show', $r->door) }}"
                                   class="inline-flex items-center gap-1.5 rounded-full pl-1.5 pr-3 py-1 text-xs font-medium border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100 transition-colors">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: {{ $r->door->color ?? '#f59e0b' }}"></span>
                                    {{ $r->door->name }}
                                    <span class="text-amber-600">· {{ $r->status?->label() }}</span>
                                    @if($r->ai_inference_id) <span title="Sugerida por IA">🤖</span> @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Línea de tiempo --}}
                @if($doorTimeline->isNotEmpty())
                    <div class="px-6 py-4">
                        <ol class="relative border-l border-gray-200 ml-2">
                            @foreach($doorTimeline as $item)
                                <li class="mb-5 ml-5 last:mb-0">
                                    <span class="absolute -left-[7px] flex items-center justify-center w-3.5 h-3.5 rounded-full ring-4 ring-white"
                                          style="background-color: {{ $item['door']?->color ?? '#6b7280' }}"></span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($item['kind'] === 'membership')
                                            <span class="text-xs" title="Voluntariado">👥</span>
                                        @else
                                            <span class="text-xs" title="Derivación">🎯</span>
                                        @endif
                                        <a href="{{ route('admin.doors.show', $item['door']) }}"
                                           class="text-sm font-medium text-gray-800 hover:text-indigo-600">{{ $item['door']?->name }}</a>
                                        @if($item['active'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">activa</span>
                                        @elseif(! empty($item['status_label']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $item['status_label'] }}</span>
                                        @endif
                                        @if(! empty($item['is_ai']))
                                            <span class="text-xs" title="Sugerida por IA">🤖</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['label'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ \Illuminate\Support\Carbon::parse($item['date'])->format('d/m/Y') }}
                                        @if($item['end_date'])
                                            &rarr; {{ \Illuminate\Support\Carbon::parse($item['end_date'])->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @else
                    <p class="px-6 py-6 text-sm text-gray-400">Esta persona aún no ha sido derivada ni se ha unido al equipo de ninguna puerta.</p>
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

            {{-- Notas y Seguimiento (CRM) --}}
            @assets
                <link rel="stylesheet" href="https://unpkg.com/trix@2/dist/trix.css">
                <script src="https://unpkg.com/trix@2/dist/trix.umd.min.js"></script>
            @endassets

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-1">Notas y Seguimiento</h3>
                <p class="text-xs text-gray-400 mb-4">Bitácora pastoral. Cada nota se envía a la IA para evaluar derivaciones.</p>

                @can('people.update')
                    {{-- Acciones rápidas --}}
                    @php($colorClasses = [
                        'rose'  => 'border-rose-200 text-rose-700 hover:bg-rose-50',
                        'amber' => 'border-amber-200 text-amber-700 hover:bg-amber-50',
                        'green' => 'border-green-200 text-green-700 hover:bg-green-50',
                    ])
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($quickActions as $key => $action)
                            <button wire:click="quickAction('{{ $key }}')"
                                    wire:confirm="¿Registrar '{{ $action['label'] }}' para {{ $person->full_name }}?"
                                    class="inline-flex items-center gap-1.5 rounded-lg border bg-white px-3 py-1.5 text-xs font-medium transition-colors {{ $colorClasses[$action['color']] ?? 'border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                                {{ $action['label'] }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Editor WYSIWYG (Trix) --}}
                    <div wire:ignore
                         x-data="{ value: @entangle('noteBody') }"
                         x-init="
                            const editor = $refs.editor;
                            editor.addEventListener('trix-change', () => { value = editor.value });
                            Livewire.on('note-cleared', () => { if (editor.editor) editor.editor.loadHTML('') });
                         ">
                        <input id="trix_{{ $person->id }}" type="hidden">
                        <trix-editor x-ref="editor" input="trix_{{ $person->id }}"
                                     class="trix-content prose prose-sm max-w-none min-h-[110px] rounded-lg border border-gray-300 focus:outline-none"></trix-editor>
                    </div>
                    @error('noteBody') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                    <div class="flex justify-end mt-2">
                        <button wire:click="addNote" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="addNote">Agregar nota</span>
                            <span wire:loading wire:target="addNote">Guardando…</span>
                        </button>
                    </div>
                @endcan

                {{-- Bitácora (CRM log) --}}
                <div class="mt-5 border-t border-gray-100 pt-4 space-y-4">
                    @forelse($notes as $note)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 mt-0.5">
                                @if($note->type?->value === 'quick_action')
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 text-xs">⚡</span>
                                @elseif($note->type?->value === 'system')
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-500 text-xs">⚙️</span>
                                @else
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs">📝</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-medium text-gray-700">{{ $note->author?->name ?? 'Sistema' }}</span>
                                    <span class="text-xs text-gray-400">{{ $note->created_at?->diffForHumans() }}</span>
                                    @if($note->type?->value === 'quick_action')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700">{{ $note->type->label() }}</span>
                                    @endif
                                </div>
                                <div class="prose prose-sm max-w-none text-sm text-gray-700 mt-1">
                                    {!! $note->body !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Sin notas registradas todavía.</p>
                    @endforelse
                </div>
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
