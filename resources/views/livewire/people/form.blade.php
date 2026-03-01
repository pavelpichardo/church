<div>
    @if($show)
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="close"></div>

        {{-- Modal --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">
                        {{ $personId ? 'Editar Persona' : 'Nueva Persona' }}
                    </h3>
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <form wire:submit="save" id="person-form" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre *</label>
                                <input type="text" wire:model="first_name"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('first_name') border-red-400 @enderror">
                                @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Apellido *</label>
                                <input type="text" wire:model="last_name"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('last_name') border-red-400 @enderror">
                                @error('last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                                <input type="email" wire:model="email"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Teléfono</label>
                                <input type="text" wire:model="phone"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de Nacimiento</label>
                                <input type="date" wire:model="birth_date"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Género</label>
                                <select wire:model="gender"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar...</option>
                                    @foreach($genders as $g)
                                        <option value="{{ $g->value }}">{{ $g->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Estado Civil</label>
                                <select wire:model="marital_status"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar...</option>
                                    @foreach($maritalStatuses as $ms)
                                        <option value="{{ $ms->value }}">{{ $ms->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Estado *</label>
                                <select wire:model="status"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('status') border-red-400 @enderror">
                                    @foreach($statuses as $s)
                                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                    @endforeach
                                </select>
                                @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Primera Visita</label>
                                <input type="date" wire:model="first_visit_date"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">¿Cómo nos encontró?</label>
                                <select wire:model="how_found_us"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar...</option>
                                    @foreach($howFoundUs as $h)
                                        <option value="{{ $h->value }}">{{ $h->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Dirección</label>
                            <input type="text" wire:model="address_line1" placeholder="Calle y número"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Ciudad</label>
                                <input type="text" wire:model="city"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Estado / Provincia</label>
                                <input type="text" wire:model="state"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Notas Pastorales</label>
                            <textarea wire:model="notes_pastoral" rows="3"
                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </form>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200">
                    <button type="button" wire:click="close"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" form="person-form"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                        {{ $personId ? 'Actualizar' : 'Crear' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
