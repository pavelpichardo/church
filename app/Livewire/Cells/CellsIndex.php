<?php

namespace App\Livewire\Cells;

use App\Domain\Cells\Actions\CreateCell;
use App\Domain\Cells\Actions\UpdateCell;
use App\Models\Cell;
use App\Models\Person;
use App\Support\Enums\CellStatus;
use App\Support\Enums\DayOfWeek;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CellsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // All form fields as strings to avoid Livewire hydration issues
    public string $name = '';
    public string $leader_id = '';
    public string $assistant_id = '';
    public string $host_id = '';
    public string $address_line1 = '';
    public string $address_line2 = '';
    public string $city = '';
    public string $state = '';
    public string $postal_code = '';
    public string $meeting_day = '';
    public string $meeting_time = '';
    public string $max_capacity = '15';
    public string $notes = '';

    protected function formFields(): array
    {
        return [
            'name', 'leader_id', 'assistant_id', 'host_id',
            'address_line1', 'address_line2', 'city', 'state', 'postal_code',
            'meeting_day', 'meeting_time', 'max_capacity', 'notes', 'editingId',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(Gate::allows('cells.create'), 403);
        $this->resetValidation();
        $this->reset($this->formFields());
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(Gate::allows('cells.update'), 403);
        $this->resetValidation();

        $cell = Cell::findOrFail($id);
        $this->editingId     = $id;
        $this->name          = $cell->name ?? '';
        $this->leader_id     = (string) ($cell->leader_id ?? '');
        $this->assistant_id  = (string) ($cell->assistant_id ?? '');
        $this->host_id       = (string) ($cell->host_id ?? '');
        $this->address_line1 = $cell->address_line1 ?? '';
        $this->address_line2 = $cell->address_line2 ?? '';
        $this->city          = $cell->city ?? '';
        $this->state         = $cell->state ?? '';
        $this->postal_code   = $cell->postal_code ?? '';
        $this->meeting_day   = $cell->meeting_day?->value ?? '';
        // Truncate "HH:MM:SS" to "HH:MM" for <input type="time"> and date_format:H:i validation
        $this->meeting_time  = $cell->meeting_time ? substr($cell->meeting_time, 0, 5) : '';
        $this->max_capacity  = (string) ($cell->max_capacity ?? 15);
        $this->notes         = $cell->notes ?? '';
        $this->showModal     = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name'          => 'required|string|max:255',
            'leader_id'     => 'required|exists:people,id',
            'assistant_id'  => 'nullable|exists:people,id',
            'host_id'       => 'nullable|exists:people,id',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'postal_code'   => 'nullable|string|max:20',
            'meeting_day'   => 'nullable|in:' . implode(',', array_column(DayOfWeek::cases(), 'value')),
            'meeting_time'  => 'nullable|date_format:H:i',
            'max_capacity'  => 'required|integer|min:2|max:50',
            'notes'         => 'nullable|string',
        ]);

        // Convert empty strings to null for DB
        $data = array_map(fn ($v) => $v === '' ? null : $v, $data);

        if ($this->editingId) {
            $cell = Cell::findOrFail($this->editingId);
            (new UpdateCell())->handle($cell, $data);
            session()->flash('success', 'Célula actualizada.');
        } else {
            (new CreateCell())->handle($data);
            session()->flash('success', 'Célula creada.');
        }

        $this->showModal = false;
        $this->reset($this->formFields());
    }

    public function delete(int $id): void
    {
        abort_unless(Gate::allows('cells.delete'), 403);
        Cell::findOrFail($id)->delete();
        session()->flash('success', 'Célula eliminada.');
    }

    public function render()
    {
        $cells = Cell::with(['leader', 'assistant', 'host'])
            ->withCount('activeMembers')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('name')
            ->paginate(20);

        $people = Person::orderBy('first_name')->get();
        $statuses = CellStatus::cases();
        $days = DayOfWeek::cases();

        return view('livewire.cells.index', compact('cells', 'people', 'statuses', 'days'));
    }
}
