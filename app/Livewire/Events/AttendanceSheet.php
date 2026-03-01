<?php

namespace App\Livewire\Events;

use App\Domain\Attendance\Actions\BulkRecordAttendance;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Person;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AttendanceSheet extends Component
{
    public Event $event;

    public array $selected = [];
    public string $search = '';

    public function mount(Event $event): void
    {
        $this->event = $event;

        // Pre-select already attended
        $this->selected = AttendanceRecord::where('event_id', $event->id)
            ->pluck('person_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function save(): void
    {
        abort_unless(Gate::allows('attendance.record'), 403);

        $personIds = array_map('intval', $this->selected);

        (new BulkRecordAttendance())->handle($this->event, $personIds);

        session()->flash('success', 'Asistencia guardada correctamente.');
    }

    public function render()
    {
        $people = Person::where('status', '!=', 'visitor')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%");
            }))
            ->orderBy('first_name')
            ->get();

        $attended = AttendanceRecord::where('event_id', $this->event->id)
            ->pluck('person_id')
            ->toArray();

        return view('livewire.events.attendance-sheet', compact('people', 'attended'));
    }
}
