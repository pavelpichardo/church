<?php

namespace App\Livewire\Discipleship;

use App\Models\Discipleship;
use App\Models\DiscipleshipAssignment;
use App\Support\Enums\AssignmentStatus;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class DiscipleshipShow extends Component
{
    use WithPagination;

    public Discipleship $discipleship;
    public string $search = '';
    public string $statusFilter = '';

    public function mount(Discipleship $discipleship): void
    {
        $this->discipleship = $discipleship;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $discipleship = $this->discipleship->load('leader');

        $assignments = DiscipleshipAssignment::with('person')
            ->where('discipleship_id', $this->discipleship->id)
            ->when($this->search, fn ($q) => $q->whereHas('person', fn ($pq) =>
                $pq->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('start_date')
            ->paginate(20);

        $allAssignments = DiscipleshipAssignment::where('discipleship_id', $this->discipleship->id)->get();

        $stats = [
            'total'       => $allAssignments->count(),
            'in_progress' => $allAssignments->where('status', AssignmentStatus::InProgress)->count(),
            'completed'   => $allAssignments->where('status', AssignmentStatus::Completed)->count(),
        ];

        $statuses = AssignmentStatus::cases();

        return view('livewire.discipleship.show', compact('discipleship', 'assignments', 'stats', 'statuses'));
    }
}
