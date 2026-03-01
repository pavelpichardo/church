<?php

namespace App\Livewire\Library;

use App\Models\MaterialLoan;
use App\Models\StudyMaterial;
use App\Support\Enums\LoanStatus;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MaterialShow extends Component
{
    use WithPagination;

    public StudyMaterial $studyMaterial;
    public string $search = '';
    public string $statusFilter = '';

    public function mount(StudyMaterial $studyMaterial): void
    {
        $this->studyMaterial = $studyMaterial;
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
        $loans = MaterialLoan::with(['person', 'assignedBy'])
            ->where('study_material_id', $this->studyMaterial->id)
            ->when($this->search, fn ($q) => $q->whereHas('person', fn ($pq) =>
                $pq->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('assigned_at')
            ->paginate(20);

        $allLoans = MaterialLoan::where('study_material_id', $this->studyMaterial->id)->get();

        $stats = [
            'total_loans' => $allLoans->count(),
            'active'      => $allLoans->whereIn('status', [LoanStatus::Borrowed->value, LoanStatus::Overdue->value])->count(),
            'overdue'     => $allLoans->where('status', LoanStatus::Overdue->value)->count(),
        ];

        $statuses = LoanStatus::cases();

        return view('livewire.library.show', compact('loans', 'stats', 'statuses'));
    }
}
