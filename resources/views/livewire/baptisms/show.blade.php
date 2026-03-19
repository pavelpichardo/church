<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.baptisms.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-800">Bautismo — {{ $baptism->date->format('d/m/Y') }}</h2>
            <p class="text-sm text-gray-500">
                @if($baptism->location) {{ $baptism->location }} &mdash; @endif
                {{ $baptism->people->count() }} {{ $baptism->people->count() === 1 ? 'persona' : 'personas' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @can('sacraments.create')
                @if($baptism->people->isNotEmpty())
                    <button wire:click="generateAllCertificates"
                            wire:confirm="¿Generar certificados para todas las personas?"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Generar Todos los Certificados
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT: People list --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Personas Bautizadas</h3>
                    @can('sacraments.create')
                        <button wire:click="openAddPerson"
                                class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar Persona
                        </button>
                    @endcan
                </div>

                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">#</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Estado</th>
                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Certificado</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($baptism->people as $i => $person)
                            @php $cert = $certificatesByPerson->get($person->id); @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.people.show', $person) }}"
                                       class="font-medium text-indigo-600 hover:text-indigo-800">
                                        {{ $person->first_name }} {{ $person->last_name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
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
                                <td class="px-4 py-3 text-center">
                                    @if($cert?->file)
                                        <a href="{{ Storage::disk($cert->file->disk)->url($cert->file->path) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Descargar
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('sacraments.create')
                                            <button wire:click="generateCertificate({{ $person->id }})"
                                                    wire:confirm="¿{{ $cert ? 'Regenerar' : 'Generar' }} certificado para {{ $person->first_name }}?"
                                                    class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                                {{ $cert ? 'Regenerar' : 'Generar' }} PDF
                                            </button>
                                            <button wire:click="removePerson({{ $person->id }})"
                                                    wire:confirm="¿Quitar a {{ $person->first_name }} {{ $person->last_name }} del bautismo?"
                                                    class="text-red-500 hover:text-red-700 text-xs font-medium">Quitar</button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    No hay personas agregadas. Use "Agregar Persona" para incluir bautizados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RIGHT: Info --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Detalles del Bautismo</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Fecha</dt>
                        <dd class="text-gray-800 font-medium">{{ $baptism->date->format('d \d\e F \d\e Y') }}</dd>
                    </div>
                    @if($baptism->location)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Lugar</dt>
                            <dd class="text-gray-800">{{ $baptism->location }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 font-medium">Pastor</dt>
                        <dd class="text-gray-800">{{ $baptism->pastor?->name ?? '—' }}</dd>
                    </div>
                    @if($baptism->notes)
                        <div>
                            <dt class="text-xs text-gray-400 font-medium">Notas</dt>
                            <dd class="text-gray-700 whitespace-pre-wrap">{{ $baptism->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Resumen</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total bautizados</span>
                        <span class="text-2xl font-bold text-gray-800">{{ $baptism->people->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Certificados generados</span>
                        <span class="text-2xl font-bold text-green-600">{{ $certificatesByPerson->count() }}</span>
                    </div>
                    @if($baptism->people->count() > 0)
                        <div class="pt-2">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full transition-all"
                                         style="width: {{ round($certificatesByPerson->count() / $baptism->people->count() * 100) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ round($certificatesByPerson->count() / $baptism->people->count() * 100) }}%</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Add Person Modal --}}
    @if($showAddModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="$set('showAddModal', false)"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Agregar Persona al Bautismo</h3>
                    <button wire:click="$set('showAddModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="addPerson" class="px-6 py-5 space-y-4">
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
                                        <span class="text-xs text-gray-400 ml-2">{{ $person->status?->label() }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showAddModal', false)"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Agregar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
