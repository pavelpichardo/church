<?php

namespace App\Livewire\Events;

use App\Domain\Events\Actions\CreateEvent;
use App\Domain\Events\Actions\UpdateEvent;
use App\Models\Event;
use App\Support\Enums\EventType;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class EventsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $title = '';
    public string $event_type = '';
    public string $starts_at = '';
    public string $ends_at = '';
    public string $location = '';
    public string $description = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(Gate::allows('events.create'), 403);
        $this->reset(['title', 'event_type', 'starts_at', 'ends_at', 'location', 'description', 'editingId']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(Gate::allows('events.update'), 403);
        $event = Event::findOrFail($id);
        $this->editingId    = $id;
        $this->title        = $event->title;
        $this->event_type   = $event->event_type?->value ?? '';
        $this->starts_at    = $event->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->ends_at      = $event->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->location     = $event->location ?? '';
        $this->description  = $event->description ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'title'      => 'required|string|max:200',
            'event_type' => 'required|in:' . implode(',', array_column(EventType::cases(), 'value')),
            'starts_at'  => 'required|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'location'   => 'nullable|string|max:200',
            'description' => 'nullable|string',
        ]);

        $data = array_map(fn ($v) => $v === '' ? null : $v, $data);

        if ($this->editingId) {
            $event = Event::findOrFail($this->editingId);
            (new UpdateEvent())->handle($event, $data);
            session()->flash('success', 'Evento actualizado.');
        } else {
            (new CreateEvent())->handle($data);
            session()->flash('success', 'Evento creado.');
        }

        $this->showModal = false;
        $this->reset(['title', 'event_type', 'starts_at', 'ends_at', 'location', 'description', 'editingId']);
    }

    public function delete(int $id): void
    {
        abort_unless(Gate::allows('events.delete'), 403);
        Event::findOrFail($id)->delete();
        session()->flash('success', 'Evento eliminado.');
    }

    public function render()
    {
        $events = Event::withCount('attendanceRecords')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('starts_at')
            ->paginate(20);

        $types = EventType::cases();

        return view('livewire.events.index', compact('events', 'types'));
    }
}
