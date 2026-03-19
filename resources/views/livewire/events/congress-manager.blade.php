<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.events.show', $this->event) }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-800">{{ $this->event->title }}</h2>
            <p class="text-sm text-gray-500">Gestión de roles y tareas del congreso</p>
        </div>
        @can('events.create')
            <button wire:click="openCreateRole"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Rol
            </button>
        @endcan
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Roles List --}}
    <div class="space-y-3">
        @forelse($roles as $role)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                {{-- Role Header --}}
                <div class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-gray-50 transition-colors"
                     wire:click="toggleRole({{ $role->id }})">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-400 transition-transform {{ $expandedRoleId === $role->id ? 'rotate-90' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ $role->name }}</h3>
                            @if($role->description)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $role->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            {{ $role->tasks_count }} tareas
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $role->assignments_count }} personas
                        </span>
                        <div class="flex items-center gap-1" wire:click.stop>
                            @can('events.update')
                                <button wire:click="openEditRole({{ $role->id }})"
                                        class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            @endcan
                            @can('events.delete')
                                <button wire:click="deleteRole({{ $role->id }})"
                                        wire:confirm="¿Eliminar el rol '{{ $role->name }}' y todas sus tareas y asignaciones?"
                                        class="p-1 text-red-400 hover:text-red-600 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>

                {{-- Expanded Content --}}
                @if($expandedRoleId === $role->id && $expandedRole)
                    <div class="border-t border-gray-200">
                        <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
                            {{-- LEFT: Tasks --}}
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Tareas</h4>
                                    @can('events.create')
                                        <button wire:click="openCreateTask({{ $role->id }})"
                                                class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Agregar Tarea
                                        </button>
                                    @endcan
                                </div>

                                @if($roleTasks->isEmpty())
                                    <p class="text-sm text-gray-400 py-4 text-center">No hay tareas definidas para este rol.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($roleTasks as $task)
                                            <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 group">
                                                <span class="mt-0.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                                    {{ match($task->phase->value) {
                                                        'before' => 'bg-amber-100 text-amber-700',
                                                        'during' => 'bg-blue-100 text-blue-700',
                                                        'after'  => 'bg-green-100 text-green-700',
                                                    } }}">
                                                    {{ $task->phase->label() }}
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-800">{{ $task->title }}</p>
                                                    @if($task->description)
                                                        <p class="text-xs text-gray-400 mt-0.5">{{ $task->description }}</p>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    @can('events.update')
                                                        <button wire:click="openEditTask({{ $task->id }})" class="p-1 text-gray-400 hover:text-gray-600">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                        </button>
                                                    @endcan
                                                    @can('events.delete')
                                                        <button wire:click="deleteTask({{ $task->id }})" wire:confirm="¿Eliminar esta tarea?" class="p-1 text-red-400 hover:text-red-600">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- RIGHT: Assignments + Task Progress --}}
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Personas Asignadas</h4>
                                    @can('events.create')
                                        <button wire:click="openAssign({{ $role->id }})"
                                                class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Asignar Persona
                                        </button>
                                    @endcan
                                </div>

                                @if($roleAssignments->isEmpty())
                                    <p class="text-sm text-gray-400 py-4 text-center">No hay personas asignadas a este rol.</p>
                                @else
                                    <div class="space-y-4">
                                        @foreach($roleAssignments as $assignment)
                                            <div class="rounded-lg border border-gray-200 p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">
                                                            {{ strtoupper(substr($assignment->person->first_name ?? '', 0, 1)) }}{{ strtoupper(substr($assignment->person->last_name ?? '', 0, 1)) }}
                                                        </span>
                                                        <div>
                                                            <a href="{{ route('admin.people.show', $assignment->person) }}" class="text-sm font-medium text-gray-800 hover:text-indigo-600">
                                                                {{ $assignment->person->first_name }} {{ $assignment->person->last_name }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @can('events.delete')
                                                        <button wire:click="removeAssignment({{ $assignment->id }})"
                                                                wire:confirm="¿Quitar a {{ $assignment->person->first_name }} de este rol?"
                                                                class="p-1 text-red-400 hover:text-red-600">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    @endcan
                                                </div>

                                                {{-- Task checklist for this person --}}
                                                @if($roleTasks->isNotEmpty())
                                                    <div class="space-y-1 mt-2">
                                                        @foreach($roleTasks as $task)
                                                            @php $isCompleted = $completedTaskIds->contains($task->id . '-' . $assignment->id); @endphp
                                                            <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer text-sm {{ $isCompleted ? 'text-gray-400' : 'text-gray-700' }}">
                                                                <input type="checkbox"
                                                                       wire:click="toggleTaskCompletion({{ $task->id }}, {{ $assignment->id }})"
                                                                       {{ $isCompleted ? 'checked' : '' }}
                                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                <span class="{{ $isCompleted ? 'line-through' : '' }}">{{ $task->title }}</span>
                                                                <span class="ml-auto inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                                                                    {{ match($task->phase->value) {
                                                                        'before' => 'bg-amber-100 text-amber-700',
                                                                        'during' => 'bg-blue-100 text-blue-700',
                                                                        'after'  => 'bg-green-100 text-green-700',
                                                                    } }}">
                                                                    {{ $task->phase->label() }}
                                                                </span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                    @php
                                                        $completedCount = $roleTasks->filter(fn ($t) => $completedTaskIds->contains($t->id . '-' . $assignment->id))->count();
                                                        $totalTasks = $roleTasks->count();
                                                    @endphp
                                                    <div class="mt-2 flex items-center gap-2">
                                                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                                                            <div class="bg-indigo-600 h-1.5 rounded-full transition-all"
                                                                 style="width: {{ $totalTasks > 0 ? round($completedCount / $totalTasks * 100) : 0 }}%"></div>
                                                        </div>
                                                        <span class="text-xs text-gray-500 whitespace-nowrap">{{ $completedCount }}/{{ $totalTasks }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-10 text-center">
                <p class="text-gray-400 text-sm">No hay roles definidos para este congreso.</p>
                <p class="text-gray-400 text-xs mt-1">Crea roles como Ujier, Logística, Alabanza, Registro, etc.</p>
            </div>
        @endforelse
    </div>

    {{-- ═══ ROLE MODAL ═══ --}}
    @if($showRoleModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showRoleModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">{{ $editingRoleId ? 'Editar' : 'Nuevo' }} Rol</h3>
                    <button wire:click="$set('showRoleModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="saveRole" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del Rol *</label>
                        <input type="text" wire:model="roleName" placeholder="Ej: Ujier, Logística, Alabanza..."
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('roleName') border-red-400 @enderror">
                        @error('roleName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                        <textarea wire:model="roleDescription" rows="2" placeholder="Descripción de las responsabilidades..."
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showRoleModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            {{ $editingRoleId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ═══ TASK MODAL ═══ --}}
    @if($showTaskModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showTaskModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">{{ $editingTaskId ? 'Editar' : 'Nueva' }} Tarea</h3>
                    <button wire:click="$set('showTaskModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="saveTask" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Título *</label>
                        <input type="text" wire:model="taskTitle" placeholder="Ej: Preparar equipo de sonido..."
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('taskTitle') border-red-400 @enderror">
                        @error('taskTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fase *</label>
                        <select wire:model="taskPhase"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach($phases as $p)
                                <option value="{{ $p->value }}">{{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                        <textarea wire:model="taskDescription" rows="2"
                                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showTaskModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            {{ $editingTaskId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ═══ ASSIGN PERSON MODAL ═══ --}}
    @if($showAssignModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showAssignModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Asignar Persona al Rol</h3>
                    <button wire:click="$set('showAssignModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="assignPerson" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar Persona *</label>
                        <input type="text" wire:model.live.debounce.300ms="personSearch"
                               placeholder="Escribe al menos 2 caracteres..."
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('personId') border-red-400 @enderror">
                        @error('personId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

                        @if($searchResults->isNotEmpty())
                            <div class="mt-1 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-48 overflow-y-auto bg-white shadow-lg">
                                @foreach($searchResults as $person)
                                    <button type="button"
                                            wire:click="selectPerson({{ $person->id }})"
                                            class="w-full px-3 py-2 text-left text-sm hover:bg-indigo-50 transition-colors">
                                        <span class="font-medium text-gray-800">{{ $person->first_name }} {{ $person->last_name }}</span>
                                        <span class="text-xs text-gray-400 ml-2">{{ $person->status?->label() }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showAssignModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Asignar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
