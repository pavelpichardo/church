<?php

namespace App\Livewire\Marriages;

use App\Models\Event;
use App\Models\Marriage;
use App\Models\Person;
use App\Models\User;
use App\Support\Enums\EventType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MarriagesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $date = '';
    public string $time = '';
    public string $location = '';
    public ?int $officiantId = null;
    public string $officiantSearch = '';
    public ?int $spouse1Id = null;
    public string $spouse1Search = '';
    public ?int $spouse2Id = null;
    public string $spouse2Search = '';
    public string $notes = '';

    // Track which search field is active
    public string $activeSearch = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $this->reset([
            'date', 'time', 'location', 'officiantId', 'officiantSearch',
            'spouse1Id', 'spouse1Search', 'spouse2Id', 'spouse2Search',
            'notes', 'editingId', 'activeSearch',
        ]);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $marriage = Marriage::with(['spouse1', 'spouse2', 'officiant'])->findOrFail($id);
        $this->editingId = $id;
        $this->date = $marriage->date->format('Y-m-d');
        $this->time = $marriage->event?->starts_at?->format('H:i') ?? '';
        $this->location = $marriage->location ?? '';
        $this->officiantId = $marriage->officiant_id;
        $this->officiantSearch = $marriage->officiant?->name ?? '';
        $this->spouse1Id = $marriage->spouse1_person_id;
        $this->spouse1Search = $marriage->spouse1 ? "{$marriage->spouse1->first_name} {$marriage->spouse1->last_name}" : '';
        $this->spouse2Id = $marriage->spouse2_person_id;
        $this->spouse2Search = $marriage->spouse2 ? "{$marriage->spouse2->first_name} {$marriage->spouse2->last_name}" : '';
        $this->notes = $marriage->notes ?? '';
        $this->activeSearch = '';
        $this->showModal = true;
    }

    public function selectSpouse1(int $id): void
    {
        $person = Person::findOrFail($id);
        $this->spouse1Id = $id;
        $this->spouse1Search = "{$person->first_name} {$person->last_name}";
        $this->activeSearch = '';
    }

    public function selectSpouse2(int $id): void
    {
        $person = Person::findOrFail($id);
        $this->spouse2Id = $id;
        $this->spouse2Search = "{$person->first_name} {$person->last_name}";
        $this->activeSearch = '';
    }

    public function selectOfficiant(int $id): void
    {
        $user = User::findOrFail($id);
        $this->officiantId = $id;
        $this->officiantSearch = $user->name;
        $this->activeSearch = '';
    }

    public function setActiveSearch(string $field): void
    {
        $this->activeSearch = $field;
        // Clear selection when user starts typing again
        match ($field) {
            'spouse1' => $this->spouse1Id = null,
            'spouse2' => $this->spouse2Id = null,
            'officiant' => $this->officiantId = null,
            default => null,
        };
    }

    public function save(): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);

        $this->validate([
            'date'      => 'required|date',
            'time'      => 'nullable|date_format:H:i',
            'location'  => 'nullable|string|max:255',
            'spouse1Id' => 'required|exists:people,id',
            'spouse2Id' => 'required|exists:people,id|different:spouse1Id',
            'notes'     => 'nullable|string',
        ], [
            'spouse1Id.required' => 'Debe seleccionar al novio.',
            'spouse2Id.required' => 'Debe seleccionar a la novia.',
            'spouse2Id.different' => 'El novio y la novia deben ser personas diferentes.',
        ]);

        if ($this->editingId) {
            $marriage = Marriage::findOrFail($this->editingId);
            $marriage->update([
                'date'               => $this->date,
                'location'           => $this->location ?: null,
                'officiant_id'       => $this->officiantId,
                'spouse1_person_id'  => $this->spouse1Id,
                'spouse2_person_id'  => $this->spouse2Id,
                'notes'              => $this->notes ?: null,
            ]);

            // Update linked event
            if ($marriage->event) {
                $startsAt = $this->time ? "{$this->date} {$this->time}" : $this->date;
                $marriage->event->update([
                    'title'    => $this->buildEventTitle(),
                    'location' => $this->location ?: null,
                    'starts_at' => $startsAt,
                ]);
            }

            session()->flash('success', 'Matrimonio actualizado.');
        } else {
            $startsAt = $this->time ? "{$this->date} {$this->time}" : $this->date;

            // Create the event
            $event = Event::create([
                'title'      => $this->buildEventTitle(),
                'event_type' => EventType::SpecialEvent,
                'starts_at'  => $startsAt,
                'location'   => $this->location ?: null,
                'description' => 'Ceremonia de matrimonio',
                'created_by' => Auth::id(),
            ]);

            Marriage::create([
                'event_id'           => $event->id,
                'date'               => $this->date,
                'location'           => $this->location ?: null,
                'officiant_id'       => $this->officiantId,
                'spouse1_person_id'  => $this->spouse1Id,
                'spouse2_person_id'  => $this->spouse2Id,
                'notes'              => $this->notes ?: null,
            ]);

            session()->flash('success', 'Matrimonio registrado.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $marriage = Marriage::findOrFail($id);
        $marriage->event?->delete();
        $marriage->delete();
        session()->flash('success', 'Matrimonio eliminado.');
    }

    private function buildEventTitle(): string
    {
        $s1 = Person::find($this->spouse1Id);
        $s2 = Person::find($this->spouse2Id);
        $name1 = $s1 ? "{$s1->first_name} {$s1->last_name}" : '?';
        $name2 = $s2 ? "{$s2->first_name} {$s2->last_name}" : '?';
        return "Matrimonio: {$name1} & {$name2}";
    }

    public function render()
    {
        $marriages = Marriage::with(['spouse1', 'spouse2', 'officiant', 'certificate'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->whereHas('spouse1', fn ($pq) =>
                    $pq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%"))
                  ->orWhereHas('spouse2', fn ($pq) =>
                    $pq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%"));
            }))
            ->orderByDesc('date')
            ->paginate(20);

        // Search results for modals
        $spouse1Results = collect();
        $spouse2Results = collect();
        $officiantResults = collect();

        if ($this->showModal && $this->activeSearch === 'spouse1' && strlen($this->spouse1Search) >= 2) {
            $spouse1Results = Person::where(function ($q) {
                $q->where('first_name', 'like', "%{$this->spouse1Search}%")
                  ->orWhere('last_name', 'like', "%{$this->spouse1Search}%");
            })->limit(10)->get();
        }

        if ($this->showModal && $this->activeSearch === 'spouse2' && strlen($this->spouse2Search) >= 2) {
            $spouse2Results = Person::where(function ($q) {
                $q->where('first_name', 'like', "%{$this->spouse2Search}%")
                  ->orWhere('last_name', 'like', "%{$this->spouse2Search}%");
            })->limit(10)->get();
        }

        if ($this->showModal && $this->activeSearch === 'officiant' && strlen($this->officiantSearch) >= 2) {
            $officiantResults = User::where('name', 'like', "%{$this->officiantSearch}%")
                ->where('is_active', true)
                ->limit(10)->get();
        }

        return view('livewire.marriages.index', compact(
            'marriages', 'spouse1Results', 'spouse2Results', 'officiantResults'
        ));
    }
}
