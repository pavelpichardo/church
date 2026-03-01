<x-layouts.app>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Dashboard</h2>
            <p class="text-sm text-gray-500">Resumen general del sistema</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-admin.stats-card label="Total Personas" :value="$stats['total_people']" color="indigo" />
            <x-admin.stats-card label="Miembros Activos" :value="$stats['active_members']" color="green" />
            <x-admin.stats-card label="Préstamos Activos" :value="$stats['active_loans']" color="yellow" />
            <x-admin.stats-card label="Eventos esta Semana" :value="$stats['events_this_week']" color="blue" />
        </div>

        {{-- Recent People --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Personas Recientes</h3>
                <a href="{{ route('admin.people.index') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Ver todas &rarr;</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentPeople as $person)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $person->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $person->email ?? '—' }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ match($person->status?->value) {
                                'active_member'      => 'bg-green-100 text-green-800',
                                'member'             => 'bg-blue-100 text-blue-800',
                                'membership_process' => 'bg-yellow-100 text-yellow-800',
                                'visitor'            => 'bg-gray-100 text-gray-600',
                                default              => 'bg-gray-100 text-gray-600',
                            } }}">
                            {{ $person->status?->label() ?? '—' }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-400">No hay personas registradas aún.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
