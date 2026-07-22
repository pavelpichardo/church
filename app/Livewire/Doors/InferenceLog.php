<?php

namespace App\Livewire\Doors;

use App\Models\DoorAiInference;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class InferenceLog extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'event')]
    public string $eventFilter = '';

    public ?int $expandedId = null;

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEventFilter(): void
    {
        $this->resetPage();
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function render()
    {
        $inferences = DoorAiInference::query()
            ->with('person:id,first_name,last_name')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->eventFilter, fn ($q) => $q->where('triggering_event_type', 'like', "%{$this->eventFilter}%"))
            ->orderByDesc('id')
            ->paginate(25);

        $totals = [
            'count_30d' => DoorAiInference::where('created_at', '>=', now()->subDays(30))->count(),
            'cost_30d' => (float) DoorAiInference::where('created_at', '>=', now()->subDays(30))->sum('cost_usd'),
            'fallback_30d' => DoorAiInference::where('created_at', '>=', now()->subDays(30))->where('status', 'fallback_used')->count(),
            'success_30d' => DoorAiInference::where('created_at', '>=', now()->subDays(30))->where('status', 'success')->count(),
        ];

        return view('livewire.doors.inference-log', compact('inferences', 'totals'));
    }
}
