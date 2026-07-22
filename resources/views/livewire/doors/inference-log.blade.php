<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.doors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Volver a Puertas</a>
            <h2 class="text-xl font-bold text-gray-800 mt-1">Log de IA — Auditoría de inferencias</h2>
            <p class="text-sm text-gray-500">Cada llamada al motor de routing queda registrada con tokens, latencia, costo y decisión.</p>
        </div>
    </div>

    {{-- 30-day totals --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wider text-gray-500">Inferencias (30d)</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totals['count_30d']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wider text-gray-500">Costo (30d)</p>
            <p class="text-2xl font-bold text-indigo-700 mt-1">${{ number_format($totals['cost_30d'], 4) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wider text-gray-500">Exitosas (30d)</p>
            <p class="text-2xl font-bold text-green-700 mt-1">{{ number_format($totals['success_30d']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-wider text-gray-500">Fallback (30d)</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($totals['fallback_30d']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <select wire:model.live="statusFilter"
                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Todos los estados</option>
            <option value="success">Éxito</option>
            <option value="failed">Falló</option>
            <option value="fallback_used">Fallback</option>
        </select>
        <input type="text" wire:model.live.debounce.300ms="eventFilter"
               placeholder="Filtrar por tipo de evento (ej. person.registered)"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 max-w-sm flex-1">
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Evento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Persona</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Modelo</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Tokens</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Latencia</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Costo</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Decisiones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($inferences as $inf)
                    <tr class="hover:bg-gray-50 cursor-pointer" wire:click="toggleExpand({{ $inf->id }})">
                        <td class="px-4 py-3 font-mono text-xs">{{ $inf->triggering_event_type }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            @if($inf->person)
                                <a href="{{ route('admin.people.show', $inf->person) }}" class="text-indigo-600 hover:underline" wire:click.stop>{{ $inf->person->first_name }} {{ $inf->person->last_name }}</a>
                            @else
                                <span class="text-gray-400 italic">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 font-mono text-xs">{{ $inf->model_used ?? '—' }}</td>
                        <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500 font-mono text-xs">
                            @if($inf->prompt_tokens)
                                {{ $inf->prompt_tokens }} in
                                @if($inf->cached_tokens > 0)<span class="text-green-600">({{ $inf->cached_tokens }} cached)</span>@endif
                                / {{ $inf->output_tokens }} out
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500 font-mono text-xs">
                            {{ $inf->latency_ms ? $inf->latency_ms.'ms' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-xs text-gray-700">
                            {{ $inf->cost_usd ? '$'.number_format($inf->cost_usd, 6) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ match($inf->status?->value) {
                                    'success'       => 'bg-green-100 text-green-800',
                                    'failed'        => 'bg-red-100 text-red-800',
                                    'fallback_used' => 'bg-amber-100 text-amber-800',
                                    default         => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $inf->status?->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-medium text-gray-700">
                            {{ count($inf->decisions ?? []) }}
                        </td>
                    </tr>
                    @if($expandedId === $inf->id)
                        <tr class="bg-gray-50">
                            <td colspan="8" class="px-4 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Decisiones</p>
                                        @forelse($inf->decisions ?? [] as $d)
                                            <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
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
                                        @empty
                                            <p class="text-sm text-gray-500 italic">Sin decisiones (sin acción aplicable).</p>
                                        @endforelse
                                    </div>
                                    @if($inf->error_message)
                                        <div>
                                            <p class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Error</p>
                                            <p class="text-sm text-red-700 font-mono bg-red-50 p-2 rounded">{{ $inf->error_message }}</p>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            Sin inferencias registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $inferences->links() }}</div>
</div>
