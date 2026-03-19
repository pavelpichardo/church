<?php

namespace App\Livewire\Baptisms;

use App\Models\Baptism;
use App\Models\Event;
use App\Models\User;
use App\Support\Enums\EventType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class BaptismsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $date = '';
    public string $time = '';
    public string $location = '';
    public ?int $pastorId = null;
    public string $pastorSearch = '';
    public string $notes = '';
    public string $activeSearch = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $this->reset(['date', 'time', 'location', 'pastorId', 'pastorSearch', 'notes', 'editingId', 'activeSearch']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $baptism = Baptism::with('pastor')->findOrFail($id);
        $this->editingId = $id;
        $this->date = $baptism->date->format('Y-m-d');
        $this->time = $baptism->event?->starts_at?->format('H:i') ?? '';
        $this->location = $baptism->location ?? '';
        $this->pastorId = $baptism->pastor_id;
        $this->pastorSearch = $baptism->pastor?->name ?? '';
        $this->notes = $baptism->notes ?? '';
        $this->activeSearch = '';
        $this->showModal = true;
    }

    public function selectPastor(int $id): void
    {
        $user = User::findOrFail($id);
        $this->pastorId = $id;
        $this->pastorSearch = $user->name;
        $this->activeSearch = '';
    }

    public function setActiveSearch(string $field): void
    {
        $this->activeSearch = $field;
        if ($field === 'pastor') {
            $this->pastorId = null;
        }
    }

    public function save(): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);

        $this->validate([
            'date'     => 'required|date',
            'time'     => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'notes'    => 'nullable|string',
        ]);

        if ($this->editingId) {
            $baptism = Baptism::findOrFail($this->editingId);
            $baptism->update([
                'date'      => $this->date,
                'location'  => $this->location ?: null,
                'pastor_id' => $this->pastorId,
                'notes'     => $this->notes ?: null,
            ]);

            if ($baptism->event) {
                $startsAt = $this->time ? "{$this->date} {$this->time}" : $this->date;
                $baptism->event->update([
                    'title'     => "Bautismo — {$baptism->date->format('d/m/Y')}",
                    'location'  => $this->location ?: null,
                    'starts_at' => $startsAt,
                ]);
            }

            session()->flash('success', 'Bautismo actualizado.');
        } else {
            $startsAt = $this->time ? "{$this->date} {$this->time}" : $this->date;

            $event = Event::create([
                'title'       => "Bautismo — " . \Carbon\Carbon::parse($this->date)->format('d/m/Y'),
                'event_type'  => EventType::SpecialEvent,
                'starts_at'   => $startsAt,
                'location'    => $this->location ?: null,
                'description' => 'Ceremonia de bautismo',
                'created_by'  => Auth::id(),
            ]);

            Baptism::create([
                'event_id'  => $event->id,
                'date'      => $this->date,
                'location'  => $this->location ?: null,
                'pastor_id' => $this->pastorId,
                'notes'     => $this->notes ?: null,
            ]);

            session()->flash('success', 'Bautismo registrado.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $baptism = Baptism::findOrFail($id);
        $baptism->event?->delete();
        $baptism->delete();
        session()->flash('success', 'Bautismo eliminado.');
    }

    public function render()
    {
        $baptisms = Baptism::with(['pastor'])
            ->withCount('people')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('location', 'like', "%{$this->search}%")
                  ->orWhereHas('people', fn ($pq) =>
                      $pq->where('first_name', 'like', "%{$this->search}%")
                         ->orWhere('last_name', 'like', "%{$this->search}%"))
                  ->orWhereHas('pastor', fn ($pq) =>
                      $pq->where('name', 'like', "%{$this->search}%"));
            }))
            ->orderByDesc('date')
            ->paginate(20);

        $pastorResults = collect();
        if ($this->showModal && $this->activeSearch === 'pastor' && strlen($this->pastorSearch) >= 2) {
            $pastorResults = User::where('name', 'like', "%{$this->pastorSearch}%")
                ->where('is_active', true)
                ->limit(10)->get();
        }

        return view('livewire.baptisms.index', compact('baptisms', 'pastorResults'));
    }
}
