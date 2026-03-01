<?php

namespace App\Livewire\Membership;

use App\Domain\Membership\Actions\AdvanceMembershipStage;
use App\Domain\Membership\Actions\ApproveMembership;
use App\Models\MembershipStage;
use App\Models\Person;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MembershipIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function advance(int $personId, int $stageId): void
    {
        abort_unless(Gate::allows('membership.advance'), 403);

        try {
            $person = Person::findOrFail($personId);
            (new AdvanceMembershipStage())->handle($person, $stageId);
            session()->flash('success', 'Etapa actualizada correctamente.');
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        }
    }

    public function approve(int $personId): void
    {
        abort_unless(Gate::allows('membership.approve'), 403);

        try {
            $person = Person::findOrFail($personId);
            (new ApproveMembership())->handle($person);
            session()->flash('success', 'Membresía aprobada correctamente.');
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        }
    }

    public function render()
    {
        $stages = MembershipStage::orderBy('order')->get();

        $search = $this->search;

        $people = Person::with(['membership.currentStage'])
            ->where(function ($q) use ($search) {
                $q->whereHas('membership')
                  ->orWhere('status', 'membership_process');
            })
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            }))
            ->orderBy('first_name')
            ->paginate(20);

        return view('livewire.membership.index', compact('people', 'stages'));
    }
}
