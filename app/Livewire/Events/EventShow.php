<?php

namespace App\Livewire\Events;

use App\Models\AttendanceRecord;
use App\Models\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class EventShow extends Component
{
    use WithPagination;

    public Event $event;
    public string $search = '';

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $event = $this->event->load('createdBy');

        $totalCount = AttendanceRecord::where('event_id', $this->event->id)->count();

        $attendees = AttendanceRecord::with('person')
            ->where('event_id', $this->event->id)
            ->when($this->search, fn ($q) => $q->whereHas('person', fn ($pq) =>
                $pq->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
            ))
            ->orderBy('checked_in_at')
            ->paginate(30);

        return view('livewire.events.show', compact('event', 'attendees', 'totalCount'));
    }
}
