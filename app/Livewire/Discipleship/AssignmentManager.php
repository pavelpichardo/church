<?php

namespace App\Livewire\Discipleship;

use App\Domain\Discipleship\Actions\AssignDiscipleship;
use App\Domain\Discipleship\Actions\CompleteDiscipleship;
use App\Models\Discipleship;
use App\Models\DiscipleshipAssignment;
use App\Models\Person;
use App\Support\Enums\AssignmentStatus;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AssignmentManager extends Component
{
    use WithPagination;

    public Discipleship $discipleship;

    public bool $showModal = false;
    public int $personId = 0;
    public string $personSearch = '';
    public string $start_date = '';
    public string $notes = '';

    public function mount(Discipleship $discipleship): void
    {
        $this->discipleship = $discipleship;
        $this->start_date   = now()->toDateString();
    }

    public function assign(): void
    {
        abort_unless(Gate::allows('discipleships.assign'), 403);

        $this->validate([
            'personId'   => 'required|integer|exists:people,id',
            'start_date' => 'required|date',
        ]);

        try {
            $person = Person::findOrFail($this->personId);
            (new AssignDiscipleship())->handle($this->discipleship, $person, [
                'start_date' => $this->start_date,
                'notes'      => $this->notes ?: null,
            ]);
            $this->showModal = false;
            $this->reset(['personId', 'personSearch', 'start_date', 'notes']);
            $this->start_date = now()->toDateString();
            session()->flash('success', 'Persona asignada al discipulado.');
        } catch (ValidationException $e) {
            $this->addError('personId', collect($e->errors())->flatten()->first());
        }
    }

    public function complete(int $id): void
    {
        abort_unless(Gate::allows('discipleships.complete'), 403);

        try {
            $assignment = DiscipleshipAssignment::findOrFail($id);
            (new CompleteDiscipleship())->handle($assignment);
            session()->flash('success', 'Discipulado completado.');
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        }
    }

    public function selectPerson(int $id, string $name): void
    {
        $this->personId     = $id;
        $this->personSearch = $name;
    }

    public function render()
    {
        $assignments = DiscipleshipAssignment::with('person')
            ->where('discipleship_id', $this->discipleship->id)
            ->orderByDesc('start_date')
            ->paginate(20);

        $people = collect();
        if (strlen($this->personSearch) >= 2 && ! $this->personId) {
            $people = Person::where('first_name', 'like', "%{$this->personSearch}%")
                ->orWhere('last_name', 'like', "%{$this->personSearch}%")
                ->limit(10)
                ->get();
        }

        return view('livewire.discipleship.assignment-manager', compact('assignments', 'people'));
    }
}
