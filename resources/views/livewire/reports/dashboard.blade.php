<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Reportes</h2>
            <p class="text-sm text-gray-500">Análisis y estadísticas de la iglesia</p>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div class="flex items-center gap-3 flex-wrap">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
            <input type="date" wire:model.live="dateFrom"
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
            <input type="date" wire:model.live="dateTo"
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="flex gap-1 overflow-x-auto -mb-px">
            @foreach([
                'attendance'     => 'Asistencia',
                'inactive'       => 'Inactivos',
                'membership'     => 'Membresía',
                'sacraments'     => 'Sacramentos',
                'discipleship'   => 'Discipulado',
                'demographics'   => 'Demografía',
                'communications' => 'Comunicaciones',
                'library'        => 'Biblioteca',
            ] as $key => $label)
                <button wire:click="setTab('{{ $key }}')"
                        class="whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                            {{ $tab === $key
                                ? 'border-indigo-600 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Report Content --}}
    <div class="mt-4">
        @if($tab === 'attendance')
            {{-- ── Attendance Report ── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Eventos</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['totalEvents']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Asistencias</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['totalAttendance']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Asistentes Únicos</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['uniqueAttendees']) }}</p>
                </div>
            </div>

            {{-- By Type --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Asistencia por Tipo de Evento</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Eventos</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Asistencia Total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Promedio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reportData['byType'] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $row['type'] }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $row['events'] }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ number_format($row['attendance']) }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $row['average'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Sin datos en este período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Monthly Trend --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Tendencia Mensual</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mes</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Eventos</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Asistencia</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Gráfico</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $maxAtt = $reportData['monthly']->max('total_attendance') ?: 1; @endphp
                        @forelse($reportData['monthly'] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $row->month }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $row->event_count }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ number_format($row->total_attendance) }}</td>
                                <td class="px-4 py-3">
                                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                                        <div class="bg-indigo-500 h-2.5 rounded-full" style="width: {{ round($row->total_attendance / $maxAtt * 100) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Top Events --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Top 10 Eventos Más Asistidos</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Evento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Asistentes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reportData['topEvents'] as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.events.show', $event) }}" class="font-medium text-gray-800 hover:text-indigo-600 hover:underline">{{ $event->title }}</a>
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $event->starts_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">{{ $event->attendance_records_count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($tab === 'inactive')
            {{-- ── Inactive Report ── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Miembros Totales</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['totalMembers']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Activos (con asistencia)</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($reportData['activeCount']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Inactivos</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($reportData['inactive']->count()) }}</p>
                </div>
            </div>

            @if($reportData['totalMembers'] > 0)
                <div class="mb-6">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ round($reportData['activeCount'] / $reportData['totalMembers'] * 100) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ round($reportData['activeCount'] / $reportData['totalMembers'] * 100, 1) }}% de los miembros asistieron en el período</p>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Miembros sin Asistencia en el Período</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Persona</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Última Asistencia</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Días Ausente</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reportData['inactive'] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.people.show', $row['person']) }}" class="font-medium text-gray-800 hover:text-indigo-600 hover:underline">
                                        {{ $row['person']->first_name }} {{ $row['person']->last_name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell capitalize">{{ $row['person']->status instanceof \App\Support\Enums\PersonStatus ? $row['person']->status->label() : str_replace('_', ' ', $row['person']->status) }}</td>
                                <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $row['last_attendance']?->format('d/m/Y') ?? 'Nunca' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($row['days_absent'] !== null)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $row['days_absent'] > 90 ? 'bg-red-100 text-red-700' : ($row['days_absent'] > 30 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                                            {{ $row['days_absent'] }} días
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Nunca</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Todos los miembros asistieron en este período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($tab === 'membership')
            {{-- ── Membership Report ── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Personas</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['totalPeople']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nuevos Visitantes (período)</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['newVisitors']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tasa de Retención</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $reportData['retentionRate'] }}%</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $reportData['convertedCount'] }} visitantes se convirtieron en miembros</p>
                </div>
            </div>

            {{-- Pipeline --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Pipeline de Membresía</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @php
                            $statusColors = [
                                'visitor' => 'bg-blue-50 border-blue-200 text-blue-700',
                                'membership_process' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
                                'member' => 'bg-green-50 border-green-200 text-green-700',
                                'active_member' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                                'inactive' => 'bg-gray-50 border-gray-200 text-gray-600',
                                'transferred' => 'bg-purple-50 border-purple-200 text-purple-700',
                            ];
                            $statusLabels = [
                                'visitor' => 'Visitante',
                                'membership_process' => 'En Proceso',
                                'member' => 'Miembro',
                                'active_member' => 'Activo',
                                'inactive' => 'Inactivo',
                                'transferred' => 'Trasladado',
                            ];
                        @endphp
                        @foreach($reportData['pipeline'] as $status => $count)
                            <div class="rounded-lg border p-4 text-center {{ $statusColors[$status] ?? 'bg-gray-50 border-gray-200 text-gray-600' }}">
                                <p class="text-2xl font-bold">{{ $count }}</p>
                                <p class="text-xs font-medium mt-1 capitalize">{{ $statusLabels[$status] ?? str_replace('_', ' ', $status) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- New by Month --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Nuevas Personas por Mes</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mes</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Cantidad</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Gráfico</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $maxNew = $reportData['newByMonth']->max() ?: 1; @endphp
                        @forelse($reportData['newByMonth'] as $month => $count)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $month }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $count }}</td>
                                <td class="px-4 py-3">
                                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                                        <div class="bg-indigo-500 h-2.5 rounded-full" style="width: {{ round($count / $maxNew * 100) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($tab === 'sacraments')
            {{-- ── Sacraments Report ── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Bautizados (período)</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($reportData['totalBaptized']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Matrimonios (período)</p>
                    <p class="text-3xl font-bold text-pink-600 mt-1">{{ number_format($reportData['marriages']->count()) }}</p>
                </div>
            </div>

            {{-- Baptisms --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Bautismos en el Período</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Lugar</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Personas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reportData['baptisms'] as $b)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    <a href="{{ route('admin.baptisms.show', $b) }}" class="hover:text-indigo-600 hover:underline">{{ $b->date->format('d/m/Y') }}</a>
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $b->place ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">{{ $b->people_count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Sin bautismos en este período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Marriages --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Matrimonios en el Período</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pareja</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Lugar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reportData['marriages'] as $m)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    <a href="{{ route('admin.marriages.show', $m) }}" class="hover:text-indigo-600 hover:underline">{{ $m->date->format('d/m/Y') }}</a>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $m->spouse1?->first_name }} {{ $m->spouse1?->last_name }}
                                    &amp;
                                    {{ $m->spouse2?->first_name }} {{ $m->spouse2?->last_name }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $m->place ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Sin matrimonios en este período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Yearly Trends --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Bautismos por Año</h3>
                    </div>
                    <div class="p-5 space-y-2">
                        @forelse($reportData['baptismsByYear'] as $year => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ $year }}</span>
                                <span class="text-sm font-bold text-blue-600">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Sin datos.</p>
                        @endforelse
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Matrimonios por Año</h3>
                    </div>
                    <div class="p-5 space-y-2">
                        @forelse($reportData['marriagesByYear'] as $year => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ $year }}</span>
                                <span class="text-sm font-bold text-pink-600">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Sin datos.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        @elseif($tab === 'discipleship')
            {{-- ── Discipleship Report ── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Asignaciones</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['total']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Completadas</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($reportData['completed']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tasa de Finalización</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $reportData['completionRate'] }}%</p>
                </div>
            </div>

            {{-- By Status --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Por Estado</h3>
                </div>
                <div class="p-5">
                    <div class="flex gap-4 flex-wrap">
                        @php
                            $dsColors = [
                                'in_progress' => 'bg-blue-50 border-blue-200 text-blue-700',
                                'completed' => 'bg-green-50 border-green-200 text-green-700',
                                'cancelled' => 'bg-red-50 border-red-200 text-red-700',
                            ];
                            $dsLabels = [
                                'in_progress' => 'En Progreso',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                            ];
                        @endphp
                        @foreach($reportData['assignments'] as $status => $count)
                            <div class="rounded-lg border p-4 text-center min-w-[120px] {{ $dsColors[$status] ?? 'bg-gray-50 border-gray-200 text-gray-600' }}">
                                <p class="text-2xl font-bold">{{ $count }}</p>
                                <p class="text-xs font-medium mt-1">{{ $dsLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- By Discipleship --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Por Discipulado</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Discipulado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Nivel</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Completados</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">En Progreso</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Cancelados</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reportData['byDiscipleship'] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $row->name }}</td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell capitalize">{{ str_replace('_', ' ', $row->level) }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $row->total }}</td>
                                <td class="px-4 py-3 text-right text-green-600 font-medium">{{ $row->completed }}</td>
                                <td class="px-4 py-3 text-right text-blue-600 hidden sm:table-cell">{{ $row->in_progress }}</td>
                                <td class="px-4 py-3 text-right text-red-500 hidden md:table-cell">{{ $row->cancelled }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($tab === 'demographics')
            {{-- ── Demographics Report ── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Personas</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['totalPeople']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Con Fecha de Nacimiento</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['withBirthdate']) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $reportData['totalPeople'] > 0 ? round($reportData['withBirthdate'] / $reportData['totalPeople'] * 100, 1) : 0 }}% del total</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                {{-- Gender --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Por Género</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @php
                            $genderLabels = ['male' => 'Masculino', 'female' => 'Femenino', 'other' => 'Otro'];
                            $genderColors = ['male' => 'bg-blue-500', 'female' => 'bg-pink-500', 'other' => 'bg-gray-400'];
                            $genderTotal = $reportData['byGender']->sum() ?: 1;
                        @endphp
                        @foreach($reportData['byGender'] as $gender => $count)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700">{{ $genderLabels[$gender] ?? ucfirst($gender) }}</span>
                                    <span class="font-medium text-gray-800">{{ $count }} ({{ round($count / $genderTotal * 100) }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="{{ $genderColors[$gender] ?? 'bg-gray-400' }} h-2 rounded-full" style="width: {{ round($count / $genderTotal * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Marital Status --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Estado Civil</h3>
                    </div>
                    <div class="p-5 space-y-2">
                        @php
                            $maritalLabels = ['single' => 'Soltero/a', 'married' => 'Casado/a', 'divorced' => 'Divorciado/a', 'widowed' => 'Viudo/a', 'separated' => 'Separado/a'];
                        @endphp
                        @foreach($reportData['byMaritalStatus'] as $status => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ $maritalLabels[$status] ?? ucfirst($status) }}</span>
                                <span class="text-sm font-bold text-gray-800">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- How Found Us --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Cómo nos Encontraron</h3>
                    </div>
                    <div class="p-5 space-y-2">
                        @foreach($reportData['byHowFound'] as $how => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 capitalize">{{ str_replace('_', ' ', $how) }}</span>
                                <span class="text-sm font-bold text-gray-800">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Age Groups --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Distribución por Edad</h3>
                </div>
                <div class="p-5">
                    @php $maxAge = $reportData['ageGroups']->max() ?: 1; @endphp
                    <div class="space-y-3">
                        @foreach($reportData['ageGroups'] as $group => $count)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700">{{ $group }}</span>
                                    <span class="font-medium text-gray-800">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3">
                                    <div class="bg-indigo-500 h-3 rounded-full" style="width: {{ round($count / $maxAge * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Upcoming Birthdays --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Cumpleaños Próximos (30 días)</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Persona</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reportData['upcomingBirthdays'] as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.people.show', $p) }}" class="font-medium text-gray-800 hover:text-indigo-600 hover:underline">
                                        {{ $p->first_name }} {{ $p->last_name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->birth_date->format('d/m') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-8 text-center text-gray-400">Sin cumpleaños próximos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($tab === 'communications')
            {{-- ── Communications Report ── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Destinatarios</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($reportData['totalRecipients']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Enviados</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($reportData['sentRecipients']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Fallidos</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($reportData['failedRecipients']) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- By Status --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Por Estado</h3>
                    </div>
                    <div class="p-5 space-y-2">
                        @php
                            $commStatusLabels = ['draft' => 'Borrador', 'scheduled' => 'Programado', 'sending' => 'Enviando', 'sent' => 'Enviado', 'partial' => 'Parcial', 'cancelled' => 'Cancelado'];
                            $commStatusColors = ['draft' => 'text-gray-600', 'scheduled' => 'text-yellow-700', 'sending' => 'text-blue-600', 'sent' => 'text-green-600', 'partial' => 'text-orange-600', 'cancelled' => 'text-red-600'];
                        @endphp
                        @foreach($reportData['byStatus'] as $status => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ $commStatusLabels[$status] ?? ucfirst($status) }}</span>
                                <span class="text-sm font-bold {{ $commStatusColors[$status] ?? 'text-gray-800' }}">{{ $count }}</span>
                            </div>
                        @endforeach
                        @if($reportData['byStatus']->isEmpty())
                            <p class="text-sm text-gray-400 text-center py-4">Sin datos.</p>
                        @endif
                    </div>
                </div>

                {{-- By Channel --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Por Canal</h3>
                    </div>
                    <div class="p-5 space-y-2">
                        @php
                            $channelLabels = ['email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'];
                        @endphp
                        @foreach($reportData['byChannel'] as $channel => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ $channelLabels[$channel] ?? ucfirst($channel) }}</span>
                                <span class="text-sm font-bold text-gray-800">{{ $count }}</span>
                            </div>
                        @endforeach
                        @if($reportData['byChannel']->isEmpty())
                            <p class="text-sm text-gray-400 text-center py-4">Sin datos.</p>
                        @endif
                    </div>
                </div>
            </div>

        @elseif($tab === 'library')
            {{-- ── Library Report ── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Préstamos Activos</p>
                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($reportData['activeLoans']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Vencidos</p>
                    <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($reportData['overdueLoans']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Devueltos</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ number_format($reportData['totalReturned']) }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Perdidos</p>
                    <p class="text-3xl font-bold text-gray-500 mt-1">{{ number_format($reportData['totalLost']) }}</p>
                </div>
            </div>

            {{-- Overdue List --}}
            @if($reportData['overdueList']->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden mb-6">
                    <div class="px-5 py-4 border-b border-red-200 bg-red-50">
                        <h3 class="font-semibold text-red-800">Préstamos Vencidos</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Material</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Persona</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($reportData['overdueList'] as $loan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $loan->studyMaterial?->title ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $loan->person?->first_name }} {{ $loan->person?->last_name }}
                                    </td>
                                    <td class="px-4 py-3 text-red-600 hidden sm:table-cell">{{ $loan->due_at?->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Top Materials --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Materiales Más Prestados</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Material</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Préstamos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($reportData['topMaterials'] as $mat)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $mat->title }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">{{ $mat->loan_count }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-4 py-8 text-center text-gray-400">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Top Readers --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Lectores Más Activos</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Persona</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Préstamos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($reportData['topReaders'] as $reader)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ $reader->name }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">{{ $reader->loan_count }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-4 py-8 text-center text-gray-400">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
