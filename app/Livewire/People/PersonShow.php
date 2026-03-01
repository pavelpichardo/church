<?php

namespace App\Livewire\People;

use App\Domain\Membership\Actions\AdvanceMembershipStage;
use App\Domain\Membership\Actions\ApproveMembership;
use App\Models\MembershipStage;
use App\Models\Person;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PersonShow extends Component
{
    public Person $person;

    public function mount(Person $person): void
    {
        $this->person = $person;
    }

    public function getListeners(): array
    {
        return ['person-saved' => '$refresh'];
    }

    public function openEdit(): void
    {
        abort_unless(Gate::allows('people.update'), 403);

        $this->dispatch('open-person-form', id: $this->person->id);
    }

    public function advance(int $stageId): void
    {
        abort_unless(Gate::allows('membership.advance'), 403);

        try {
            (new AdvanceMembershipStage())->handle($this->person, $stageId);
            $this->person->refresh();
            session()->flash('success', 'Etapa avanzada correctamente.');
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        }
    }

    public function approve(): void
    {
        abort_unless(Gate::allows('membership.approve'), 403);

        try {
            (new ApproveMembership())->handle($this->person);
            $this->person->refresh();
            session()->flash('success', 'Membresía aprobada correctamente.');
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        }
    }

    public function render()
    {
        $person = Person::with([
            'membership.currentStage',
            'membershipHistory.fromStage',
            'membershipHistory.toStage',
            'membershipHistory.changedBy',
            'discipleshipAssignments.discipleship',
            'materialLoans.studyMaterial',
            'attendanceRecords.event',
        ])->findOrFail($this->person->id);

        $stages = MembershipStage::orderBy('order')->get();

        $activeLoans = $person->materialLoans->filter(
            fn ($l) => in_array($l->status?->value, ['borrowed', 'overdue'])
        );

        $pastLoans = $person->materialLoans->filter(
            fn ($l) => ! in_array($l->status?->value, ['borrowed', 'overdue'])
        )->take(5);

        $recentAttendance = $person->attendanceRecords->sortByDesc('checked_in_at')->take(10);

        return view('livewire.people.show', compact('person', 'stages', 'activeLoans', 'pastLoans', 'recentAttendance'));
    }
}
