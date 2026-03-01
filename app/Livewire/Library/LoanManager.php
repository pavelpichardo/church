<?php

namespace App\Livewire\Library;

use App\Domain\Library\Actions\LoanMaterial;
use App\Domain\Library\Actions\ReturnMaterial;
use App\Models\MaterialLoan;
use App\Models\Person;
use App\Models\StudyMaterial;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class LoanManager extends Component
{
    use WithPagination;

    public StudyMaterial $studyMaterial;

    public bool $showModal = false;
    public int $personId = 0;
    public string $personSearch = '';
    public string $due_at = '';
    public string $notes = '';

    public function mount(StudyMaterial $studyMaterial): void
    {
        $this->studyMaterial = $studyMaterial;
        $this->due_at = now()->addDays(14)->toDateString();
    }

    public function loan(): void
    {
        abort_unless(Gate::allows('library.loan'), 403);

        $this->validate([
            'personId' => 'required|integer|exists:people,id',
            'due_at'   => 'nullable|date|after:today',
        ]);

        try {
            $person = Person::findOrFail($this->personId);
            (new LoanMaterial())->handle($this->studyMaterial, $person, [
                'due_at' => $this->due_at ?: null,
                'notes'  => $this->notes ?: null,
            ]);
            $this->showModal = false;
            $this->reset(['personId', 'personSearch', 'notes']);
            $this->due_at = now()->addDays(14)->toDateString();
            session()->flash('success', 'Préstamo registrado.');
        } catch (ValidationException $e) {
            $this->addError('personId', collect($e->errors())->flatten()->first());
        }
    }

    public function returnLoan(int $id): void
    {
        abort_unless(Gate::allows('library.return'), 403);

        try {
            $loan = MaterialLoan::findOrFail($id);
            (new ReturnMaterial())->handle($loan);
            session()->flash('success', 'Devolución registrada.');
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
        $loans = MaterialLoan::with('person')
            ->where('study_material_id', $this->studyMaterial->id)
            ->orderByDesc('assigned_at')
            ->paginate(20);

        $people = collect();
        if (strlen($this->personSearch) >= 2 && ! $this->personId) {
            $people = Person::where('first_name', 'like', "%{$this->personSearch}%")
                ->orWhere('last_name', 'like', "%{$this->personSearch}%")
                ->limit(10)
                ->get();
        }

        return view('livewire.library.loan-manager', compact('loans', 'people'));
    }
}
