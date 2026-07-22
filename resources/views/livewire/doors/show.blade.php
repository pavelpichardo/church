<div class="space-y-6">
    {{-- Header --}}
    <div>
        <a href="{{ route('admin.doors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Volver a Puertas</a>
        <div class="flex items-start justify-between mt-1">
            <div class="flex items-start gap-3">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl text-lg font-bold text-white flex-shrink-0"
                      style="background-color: {{ $door->color ?? '#6b7280' }}">
                    {{ $door->order }}
                </span>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $door->name }}</h2>
                    <p class="text-sm text-gray-500 max-w-2xl">{{ $door->description }}</p>
                </div>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p><span class="font-medium">{{ $door->active_members_count }}</span> voluntarios activos</p>
                <p><span class="font-medium">{{ $door->open_referrals_count }}</span> necesidades abiertas</p>
                <p><span class="font-medium">{{ $door->unread_alerts_count }}</span> alertas sin leer</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex gap-6 overflow-x-auto">
            @foreach([
                'team'       => 'Equipo',
                'activities' => 'Actividades',
                'referrals'  => 'Necesidades',
                'alerts'     => 'Alertas',
                'rules'      => 'Reglas',
                'reports'    => 'Reportes',
            ] as $key => $label)
                <button wire:click="$set('activeTab', '{{ $key }}')"
                        class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium transition-colors
                            {{ $activeTab === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ $label }}
                    @if($key === 'referrals' && $referrals->count() > 0)
                        <span class="ml-1 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">{{ $referrals->count() }}</span>
                    @endif
                    @if($key === 'alerts' && $door->unread_alerts_count > 0)
                        <span class="ml-1 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">{{ $door->unread_alerts_count }}</span>
                    @endif
                </button>
            @endforeach
        </nav>
    </div>

    {{-- ─── Tab: Equipo ─── --}}
    @if($activeTab === 'team')
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Voluntarios activos</h3>
                @can('door_members.manage')
                    <button wire:click="openAddMember"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
                        + Asignar voluntario
                    </button>
                @endcan
            </div>

            @forelse($members as $m)
                <div class="bg-white rounded-lg border border-gray-200 p-3 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">{{ $m->person->full_name }}</p>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium
                                {{ match($m->role?->value) {
                                    'leader'    => 'bg-indigo-100 text-indigo-800',
                                    'co_leader' => 'bg-blue-100 text-blue-800',
                                    default     => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $m->role?->label() }}
                            </span>
                            @if($m->joined_at)
                                <span>desde {{ $m->joined_at->format('M Y') }}</span>
                            @endif
                        </div>
                    </div>
                    @can('door_members.manage')
                        <button wire:click="removeMember({{ $m->id }})"
                                wire:confirm="¿Retirar a {{ $m->person->full_name }} de este equipo?"
                                class="text-sm text-red-600 hover:text-red-800">Retirar</button>
                    @endcan
                </div>
            @empty
                <p class="text-sm text-gray-500 italic">Aún no hay voluntarios asignados a esta puerta.</p>
            @endforelse
        </div>
    @endif

    {{-- ─── Tab: Actividades ─── --}}
    @if($activeTab === 'activities')
        <div class="space-y-3">
            <h3 class="font-semibold text-gray-800">Actividades planificadas y realizadas</h3>
            @forelse($activities as $a)
                <div class="bg-white rounded-lg border border-gray-200 p-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800">{{ $a->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $a->scheduled_at?->format('d M Y · H:i') ?? 'Sin fecha' }}
                                @if($a->location) · {{ $a->location }} @endif
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ match($a->status?->value) {
                                'planned'     => 'bg-blue-100 text-blue-800',
                                'in_progress' => 'bg-amber-100 text-amber-800',
                                'completed'   => 'bg-green-100 text-green-800',
                                'cancelled'   => 'bg-gray-100 text-gray-500',
                                default       => 'bg-gray-100 text-gray-600',
                            } }}">
                            {{ $a->status?->label() }}
                        </span>
                    </div>
                    @if($a->description)
                        <p class="text-sm text-gray-600 mt-2">{{ $a->description }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500 italic">Sin actividades registradas. Use la API <code class="text-xs">POST /api/v1/doors/{{ $door->id }}/activities</code> o el panel de administración.</p>
            @endforelse
        </div>
    @endif

    {{-- ─── Tab: Necesidades ─── --}}
    @if($activeTab === 'referrals')
        <div class="space-y-3">
            <h3 class="font-semibold text-gray-800">Necesidades abiertas</h3>
            @forelse($referrals as $r)
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium
                                    {{ match($r->priority?->value) {
                                        'urgent' => 'bg-red-100 text-red-800',
                                        'high'   => 'bg-amber-100 text-amber-800',
                                        'normal' => 'bg-gray-100 text-gray-700',
                                        'low'    => 'bg-gray-50 text-gray-500',
                                        default  => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ $r->priority?->label() }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium
                                    {{ match($r->status?->value) {
                                        'pending'        => 'bg-blue-100 text-blue-800',
                                        'in_progress'    => 'bg-amber-100 text-amber-800',
                                        'pending_review' => 'bg-purple-100 text-purple-800',
                                        default          => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ $r->status?->label() }}
                                </span>
                                @if($r->ai_inference_id)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-medium bg-indigo-50 text-indigo-700">
                                        🤖 IA
                                        @if($r->ai_confidence)
                                            <span class="font-mono">{{ number_format($r->ai_confidence, 2) }}</span>
                                        @endif
                                    </span>
                                @endif
                                @if($r->due_date)
                                    <span class="text-gray-500">vence {{ $r->due_date->format('d M') }}</span>
                                @endif
                            </div>

                            <h4 class="font-semibold text-gray-800 mt-2">
                                <a href="{{ route('admin.people.show', $r->person) }}" class="hover:underline">{{ $r->person->full_name }}</a>
                            </h4>
                            <p class="text-xs text-gray-500">Categoría: {{ $r->category ?? '—' }} · creada {{ $r->created_at?->diffForHumans() }}</p>

                            @if($r->ai_reasoning)
                                <p class="text-sm text-gray-700 mt-2 italic border-l-2 border-indigo-300 pl-3">
                                    "{{ $r->ai_reasoning }}"
                                </p>
                            @endif

                            @if($r->assignedTo)
                                <p class="text-xs text-gray-500 mt-2">Asignada a <span class="font-medium">{{ $r->assignedTo->full_name }}</span></p>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 flex-shrink-0">
                            @if($r->status->value === 'pending_review')
                                @can('referrals.review_pending')
                                    <button wire:click="approveReferral({{ $r->id }})"
                                            class="inline-flex items-center justify-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">
                                        ✓ Aprobar
                                    </button>
                                    <button wire:click="rejectReferral({{ $r->id }})"
                                            wire:confirm="¿Rechazar esta sugerencia de IA?"
                                            class="inline-flex items-center justify-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        ✕ Rechazar
                                    </button>
                                @endcan
                            @elseif($r->status->value === 'pending')
                                @can('referrals.assign')
                                    <button wire:click="startReferral({{ $r->id }})"
                                            class="inline-flex items-center justify-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
                                        Comenzar
                                    </button>
                                @endcan
                            @endif
                            @if(in_array($r->status->value, ['pending', 'in_progress']))
                                @can('referrals.close')
                                    <button wire:click="completeReferral({{ $r->id }})"
                                            wire:confirm="¿Marcar esta derivación como completada?"
                                            class="inline-flex items-center justify-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Completar
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 italic">No hay necesidades abiertas para esta puerta.</p>
            @endforelse
        </div>
    @endif

    {{-- ─── Tab: Alertas ─── --}}
    @if($activeTab === 'alerts')
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Alertas del equipo</h3>
                @if($door->unread_alerts_count > 0)
                    @can('door_alerts.manage')
                        <button wire:click="markAllAlertsRead"
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
                            Marcar todas como leídas
                        </button>
                    @endcan
                @endif
            </div>

            @forelse($alerts as $alert)
                <div class="bg-white rounded-lg border {{ $alert->read_at ? 'border-gray-200' : 'border-amber-200 bg-amber-50/40' }} p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium
                                    {{ match($alert->severity?->value) {
                                        'critical' => 'bg-red-100 text-red-800',
                                        'warning'  => 'bg-amber-100 text-amber-800',
                                        default    => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ $alert->severity?->label() }}
                                </span>
                                <code class="text-xs text-gray-500">{{ $alert->type }}</code>
                                @if(! $alert->read_at)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium bg-amber-100 text-amber-700">nueva</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-800 mt-2">{{ $alert->message }}</p>
                            @if($alert->referral && $alert->referral->person)
                                <p class="text-xs text-gray-500 mt-1">
                                    Sobre <a href="{{ route('admin.people.show', $alert->referral->person) }}" class="text-indigo-600 hover:underline">{{ $alert->referral->person->full_name }}</a>
                                </p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">{{ $alert->created_at?->diffForHumans() }}</p>
                        </div>
                        @if(! $alert->read_at)
                            @can('door_alerts.manage')
                                <button wire:click="markAlertRead({{ $alert->id }})"
                                        class="text-sm text-indigo-600 hover:text-indigo-800 flex-shrink-0">Marcar leída</button>
                            @endcan
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 italic">Sin alertas para esta puerta.</p>
            @endforelse
        </div>
    @endif

    {{-- ─── Tab: Reglas ─── --}}
    @if($activeTab === 'rules')
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Reglas de routing (lenguaje natural)</h3>
                <div class="flex gap-2">
                    @can('door_rules.manage')
                        <button wire:click="openDryRun"
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">
                            🧪 Probar con persona…
                        </button>
                        <button wire:click="openCreateRule"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
                            + Nueva regla
                        </button>
                    @endcan
                </div>
            </div>

            @forelse($rules as $rule)
                <div class="bg-white rounded-lg border border-gray-200 p-4 {{ ! $rule->is_enabled ? 'opacity-60' : '' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-semibold text-gray-800">{{ $rule->name }}</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ match($rule->priority_hint?->value) {
                                        'urgent' => 'bg-red-100 text-red-800',
                                        'high'   => 'bg-amber-100 text-amber-800',
                                        'normal' => 'bg-gray-100 text-gray-700',
                                        'low'    => 'bg-gray-50 text-gray-500',
                                        default  => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ $rule->priority_hint?->label() }}
                                </span>
                                @if(! $rule->is_enabled)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">desactivada</span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-700 mt-2">{{ $rule->description }}</p>

                            @if(! empty($rule->event_types))
                                <div class="flex items-center gap-1 mt-2 flex-wrap">
                                    <span class="text-xs text-gray-500">Eventos:</span>
                                    @foreach($rule->event_types as $et)
                                        <code class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-700">{{ $et }}</code>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @can('door_rules.manage')
                            <div class="flex flex-col gap-1 flex-shrink-0">
                                <button wire:click="toggleRule({{ $rule->id }})"
                                        class="text-xs px-2 py-1 rounded {{ $rule->is_enabled ? 'text-gray-600 hover:bg-gray-100' : 'text-green-700 hover:bg-green-50' }}">
                                    {{ $rule->is_enabled ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button wire:click="openEditRule({{ $rule->id }})"
                                        class="text-xs px-2 py-1 rounded text-indigo-600 hover:bg-indigo-50">Editar</button>
                                <button wire:click="deleteRule({{ $rule->id }})"
                                        wire:confirm="¿Eliminar esta regla?"
                                        class="text-xs px-2 py-1 rounded text-red-600 hover:bg-red-50">Eliminar</button>
                            </div>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 italic">Esta puerta aún no tiene reglas. Cree una para que el motor de IA sepa cuándo derivar.</p>
            @endforelse
        </div>
    @endif

    {{-- ─── Tab: Reportes ─── --}}
    @if($activeTab === 'reports')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500">Abiertas</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $door->open_referrals_count }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500">Completadas</p>
                <p class="text-2xl font-bold text-green-700 mt-1">{{ $closedCount }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500">Por IA</p>
                <p class="text-2xl font-bold text-indigo-700 mt-1">{{ $aiCount }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500">Manuales</p>
                <p class="text-2xl font-bold text-gray-700 mt-1">{{ $manualCount }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 col-span-2 md:col-span-4">
                <p class="text-xs uppercase tracking-wider text-gray-500">Costo IA acumulado para esta puerta</p>
                <p class="text-2xl font-bold text-indigo-700 mt-1">${{ number_format((float) $totalCost, 4) }}</p>
                <p class="text-xs text-gray-500 mt-1">Suma de inferencias que generaron al menos una decisión para esta puerta.</p>
            </div>
        </div>
    @endif

    {{-- ─── Modal: Rule editor ─── --}}
    @if($showRuleModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showRuleModal', false)">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">{{ $editingRuleId ? 'Editar regla' : 'Nueva regla' }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Las reglas se escriben en español natural y son interpretadas por el motor de IA.</p>
                </div>
                <form wire:submit="saveRule" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" wire:model="ruleName"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="ej. Bienvenida a visitantes nuevos">
                        @error('ruleName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Descripción en lenguaje natural
                            <span class="text-xs text-gray-500 font-normal">— qué debe pasar y cuándo</span>
                        </label>
                        <textarea wire:model="ruleDescription" rows="4"
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                  placeholder="ej. Cualquier visitante registrado por primera vez debe recibir un contacto personal de bienvenida en los primeros 3 días."></textarea>
                        @error('ruleDescription') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prioridad sugerida</label>
                            <select wire:model="rulePriorityHint"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach($priorities as $p)
                                    <option value="{{ $p->value }}">{{ $p->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" wire:model="ruleEnabled" class="rounded border-gray-300 text-indigo-600">
                                Regla activa
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipos de eventos (opcional)
                        </label>
                        <input type="text" wire:model="ruleEventTypes" list="door-event-types"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="Haz clic en un evento abajo o escríbelo">
                        <datalist id="door-event-types">
                            @foreach($eventCatalog as $slug => $label)
                                <option value="{{ $slug }}">{{ $label }}</option>
                            @endforeach
                        </datalist>

                        {{-- Sugerencias clickeables --}}
                        <div class="mt-2">
                            <div class="flex items-center justify-between mb-1.5">
                                <p class="text-xs text-gray-500">Eventos disponibles — haz clic para agregar:</p>
                                @if($ruleEventTypes !== '')
                                    <button type="button" wire:click="clearEventTypes"
                                            class="text-xs text-gray-400 hover:text-red-600">limpiar</button>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @php($selected = collect(explode(',', $ruleEventTypes))->map(fn($e) => trim($e))->filter()->all())
                                @foreach($eventCatalog as $slug => $label)
                                    @php($isSelected = in_array($slug, $selected, true))
                                    <button type="button" wire:click="addEventType('{{ $slug }}')"
                                            title="{{ $label }}"
                                            @class([
                                                'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium border transition-colors',
                                                'bg-indigo-600 text-white border-indigo-600' => $isSelected,
                                                'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' => ! $isSelected,
                                            ])>
                                        @if($isSelected)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                        <span class="font-mono">{{ $slug }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-400 mt-2">
                                Si lo dejas vacío, la regla aplica a <span class="font-medium">todos</span> los eventos.
                                Cada slug en la lista representa una situación detectable del sistema.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="$set('showRuleModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancelar</button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">
                            {{ $editingRuleId ? 'Guardar cambios' : 'Crear regla' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ─── Modal: Add member ─── --}}
    @if($showMemberModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showMemberModal', false)">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">Asignar voluntario</h3>
                </div>
                <form wire:submit="addMember" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Persona</label>
                        <select wire:model="memberPersonId"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— elegir persona —</option>
                            @foreach($people as $p)
                                <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                            @endforeach
                        </select>
                        @error('memberPersonId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select wire:model="memberRole"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($memberRoles as $r)
                                <option value="{{ $r->value }}">{{ $r->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="$set('showMemberModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cancelar</button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ─── Modal: Dry-run ─── --}}
    @if($showDryRunModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showDryRunModal', false)">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">🧪 Probar reglas con persona real</h3>
                    <p class="text-sm text-gray-500 mt-1">Simula un evento sin crear referrals reales. Útil para validar reglas escritas en lenguaje natural antes de activarlas.</p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Persona</label>
                        <select wire:model="dryRunPersonId"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— elegir persona —</option>
                            @foreach($people as $p)
                                <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de evento</label>
                        <input type="text" wire:model="dryRunEventType"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="person.registered">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" wire:model="dryRunUseFallback" class="rounded border-gray-300 text-indigo-600">
                        Forzar fallback determinístico (no llamar a Claude)
                    </label>

                    <button wire:click="runDryRun" wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                        <span wire:loading.remove>Ejecutar dry-run</span>
                        <span wire:loading>Ejecutando…</span>
                    </button>

                    @if($dryRunError)
                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700 font-medium">Error</p>
                            <p class="text-xs text-red-600 font-mono mt-1">{{ $dryRunError }}</p>
                        </div>
                    @endif

                    @if($dryRunResult)
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700">Resultado (sin persistir)</h4>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    @if(!empty($dryRunResult['audit']['model_used']))
                                        <span class="font-mono">{{ $dryRunResult['audit']['model_used'] }}</span>
                                    @endif
                                    @if(!empty($dryRunResult['audit']['latency_ms']))
                                        <span>{{ $dryRunResult['audit']['latency_ms'] }}ms</span>
                                    @endif
                                    @if(!empty($dryRunResult['audit']['cost_usd']))
                                        <span>${{ number_format($dryRunResult['audit']['cost_usd'], 6) }}</span>
                                    @endif
                                </div>
                            </div>

                            @if(empty($dryRunResult['decisions']))
                                <p class="text-sm text-gray-500 italic">Sin acciones aplicables.</p>
                            @else
                                @foreach($dryRunResult['decisions'] as $d)
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                        <div class="flex items-center gap-2 flex-wrap text-xs">
                                            <span class="font-mono font-bold">{{ $d['door_code'] ?? '?' }}</span>
                                            <span class="text-gray-500">·</span>
                                            <span>{{ $d['action'] ?? '?' }}</span>
                                            <span class="text-gray-500">·</span>
                                            <span class="font-medium">{{ $d['category'] ?? '?' }}</span>
                                            <span class="text-gray-500">·</span>
                                            <span class="font-mono">conf: {{ number_format($d['confidence'] ?? 0, 2) }}</span>
                                            <span class="text-gray-500">·</span>
                                            <span class="font-mono">{{ $d['priority'] ?? '?' }}</span>
                                        </div>
                                        @if(!empty($d['reasoning']))
                                            <p class="text-sm text-gray-700 mt-2 italic">"{{ $d['reasoning'] }}"</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-2 p-4 border-t border-gray-200 bg-gray-50">
                    <button wire:click="$set('showDryRunModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg">{{ session('success') }}</div>
    @endif
</div>
