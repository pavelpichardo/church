<?php

namespace App\Livewire\People;

use App\Domain\Membership\Actions\AdvanceMembershipStage;
use App\Domain\Membership\Actions\ApproveMembership;
use App\Domain\People\Actions\AddPersonNote;
use App\Events\PersonContactFailed;
use App\Events\PersonHealthStatusReported;
use App\Events\PersonNoteAdded;
use App\Events\PersonReturnedAfterAbsence;
use App\Models\MembershipStage;
use App\Models\Person;
use App\Support\Enums\PersonNoteType;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PersonShow extends Component
{
    public Person $person;

    public string $noteBody = '';

    public function mount(Person $person): void
    {
        $this->person = $person;
    }

    /**
     * Quick actions available in the follow-up panel.
     * Each fires a routing-trigger event that can be targeted by door rules.
     *
     * @return array<string, array{label: string, body: string, color: string}>
     */
    public function quickActions(): array
    {
        return [
            'health' => [
                'label' => 'Reportar enfermo',
                'body' => 'Reportado con una situación de salud / enfermedad que requiere atención.',
                'color' => 'rose',
            ],
            'contact_failed' => [
                'label' => 'No se pudo contactar',
                'body' => 'Se intentó contactar a la persona sin éxito.',
                'color' => 'amber',
            ],
            'returned' => [
                'label' => 'Regresó a la iglesia',
                'body' => 'La persona regresó a la iglesia tras un tiempo de ausencia.',
                'color' => 'green',
            ],
        ];
    }

    public function addNote(): void
    {
        abort_unless(Gate::allows('people.update'), 403);

        $body = trim(strip_tags($this->noteBody, '<div><br><strong><em><ul><ol><li><a><h1>'));

        $this->validate(
            ['noteBody' => 'required|string'],
            ['noteBody.required' => 'La nota no puede estar vacía.'],
        );

        if (trim(strip_tags($this->noteBody)) === '') {
            $this->addError('noteBody', 'La nota no puede estar vacía.');
            return;
        }

        (new AddPersonNote())->handle($this->person, $this->noteBody, PersonNoteType::Note);

        // Send the note + profile to the AI to evaluate possible door routing.
        event(new PersonNoteAdded($this->person, $this->noteBody));

        $this->noteBody = '';
        $this->dispatch('note-cleared');
        session()->flash('success', 'Nota agregada. La IA evaluará si sugiere alguna puerta.');
    }

    public function quickAction(string $key): void
    {
        abort_unless(Gate::allows('people.update'), 403);

        $actions = $this->quickActions();
        if (! isset($actions[$key])) {
            return;
        }

        (new AddPersonNote())->handle(
            $this->person,
            $actions[$key]['body'],
            PersonNoteType::QuickAction,
            $key,
        );

        match ($key) {
            'health' => event(new PersonHealthStatusReported($this->person, $actions[$key]['body'], 'high')),
            'contact_failed' => event(new PersonContactFailed($this->person, $actions[$key]['body'])),
            'returned' => event(new PersonReturnedAfterAbsence($this->person, 0)),
            default => null,
        };

        session()->flash('success', $actions[$key]['label'].' registrado.');
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
            'doorReferrals.door',
            'doorMemberships.door',
            'notes.author',
        ])->findOrFail($this->person->id);

        $stages = MembershipStage::orderBy('order')->get();
        $notes = $person->notes;
        $quickActions = $this->quickActions();

        $activeLoans = $person->materialLoans->filter(
            fn ($l) => in_array($l->status?->value, ['borrowed', 'overdue'])
        );

        $pastLoans = $person->materialLoans->filter(
            fn ($l) => ! in_array($l->status?->value, ['borrowed', 'overdue'])
        )->take(5);

        $recentAttendance = $person->attendanceRecords->sortByDesc('checked_in_at')->take(10);

        // Puertas: unified chronological timeline of team participation + referrals.
        $currentMemberships = $person->doorMemberships->whereNull('left_at');
        $openReferrals = $person->doorReferrals->filter(
            fn ($r) => in_array($r->status?->value, ['pending', 'in_progress', 'pending_review'])
        );

        $doorTimeline = collect();

        foreach ($person->doorMemberships as $m) {
            $doorTimeline->push([
                'date' => $m->joined_at ?? $m->created_at,
                'kind' => 'membership',
                'door' => $m->door,
                'label' => 'Se unió al equipo como '.($m->role?->label() ?? 'voluntario'),
                'active' => $m->left_at === null,
                'end_date' => $m->left_at,
            ]);
        }

        foreach ($person->doorReferrals as $r) {
            $doorTimeline->push([
                'date' => $r->created_at,
                'kind' => 'referral',
                'door' => $r->door,
                'label' => 'Derivada'.($r->category ? ' · '.$r->category : ''),
                'active' => in_array($r->status?->value, ['pending', 'in_progress', 'pending_review']),
                'end_date' => $r->completed_at,
                'status_label' => $r->status?->label(),
                'is_ai' => $r->ai_inference_id !== null,
            ]);
        }

        $doorTimeline = $doorTimeline->sortByDesc('date')->values();

        return view('livewire.people.show', compact(
            'person', 'stages', 'activeLoans', 'pastLoans', 'recentAttendance',
            'currentMemberships', 'openReferrals', 'doorTimeline',
            'notes', 'quickActions',
        ));
    }
}
