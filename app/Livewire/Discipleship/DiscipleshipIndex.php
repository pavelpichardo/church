<?php

namespace App\Livewire\Discipleship;

use App\Models\Discipleship;
use App\Support\Enums\DiscipleshipLevel;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class DiscipleshipIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $level = '';
    public string $duration_weeks = '';
    public string $description = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(Gate::allows('discipleships.create'), 403);
        $this->reset(['name', 'level', 'duration_weeks', 'description', 'editingId']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(Gate::allows('discipleships.update'), 403);
        $d = Discipleship::findOrFail($id);
        $this->editingId      = $id;
        $this->name           = $d->name;
        $this->level          = $d->level?->value ?? '';
        $this->duration_weeks = (string) ($d->duration_weeks ?? '');
        $this->description    = $d->description ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name'           => 'required|string|max:150',
            'level'          => 'required|in:' . implode(',', array_column(DiscipleshipLevel::cases(), 'value')),
            'duration_weeks' => 'nullable|integer|min:1',
            'description'    => 'nullable|string',
        ]);

        if ($this->editingId) {
            Discipleship::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Discipulado actualizado.');
        } else {
            Discipleship::create($data);
            session()->flash('success', 'Discipulado creado.');
        }

        $this->showModal = false;
        $this->reset(['name', 'level', 'duration_weeks', 'description', 'editingId']);
    }

    public function delete(int $id): void
    {
        abort_unless(Gate::allows('discipleships.delete'), 403);
        Discipleship::findOrFail($id)->delete();
        session()->flash('success', 'Discipulado eliminado.');
    }

    public function render()
    {
        $discipleships = Discipleship::withCount('assignments')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        $levels = DiscipleshipLevel::cases();

        return view('livewire.discipleship.index', compact('discipleships', 'levels'));
    }
}
