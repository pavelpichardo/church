<?php

namespace App\Livewire\People;

use App\Domain\People\Actions\DeletePerson;
use App\Models\Person;
use App\Support\Enums\PersonStatus;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PeopleIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showModal = false;
    public ?int $editingId = null;

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
        $this->editingId = null;
        $this->showModal = true;
        $this->dispatch('open-person-form', id: null);
    }

    public function openEdit(int $id): void
    {
        $this->editingId = $id;
        $this->showModal = true;
        $this->dispatch('open-person-form', id: $id);
    }

    public function delete(int $id): void
    {
        abort_unless(Gate::allows('people.delete'), 403);

        $person = Person::findOrFail($id);
        (new DeletePerson())->handle($person);

        session()->flash('success', 'Persona eliminada correctamente.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
    }

    public function render()
    {
        $people = Person::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('first_name')
            ->paginate(20);

        $statuses = PersonStatus::cases();

        return view('livewire.people.index', compact('people', 'statuses'));
    }
}
