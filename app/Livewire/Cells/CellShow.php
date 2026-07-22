<?php

namespace App\Livewire\Cells;

use App\Domain\Cells\Actions\MultiplyCell;
use App\Events\CellMemberAdded;
use App\Models\Cell;
use App\Models\Person;
use App\Support\Enums\DayOfWeek;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CellShow extends Component
{
    public Cell $cell;

    // Add member
    public string $newMemberId = '';

    // Multiply modal
    public bool $showMultiplyModal = false;
    public string $newCellName = '';
    public string $newHostId = '';
    public string $childAssistantId = '';
    public string $parentNewAssistantId = '';
    public string $newAddressLine1 = '';
    public string $newAddressLine2 = '';
    public string $newCity = '';
    public string $newState = '';
    public string $newPostalCode = '';
    public string $newMeetingDay = '';
    public string $newMeetingTime = '';
    public array $selectedMemberIds = [];

    public function mount(Cell $cell): void
    {
        $this->cell = $cell;
    }

    public function addMember(): void
    {
        abort_unless(Gate::allows('cells.update'), 403);

        $this->validate([
            'newMemberId' => 'required|exists:people,id',
        ]);

        $alreadyActive = $this->cell->activeMembers()->where('people.id', $this->newMemberId)->exists();

        $this->cell->members()->syncWithoutDetaching([
            $this->newMemberId => ['joined_at' => now()],
        ]);

        if (! $alreadyActive) {
            $person = Person::find($this->newMemberId);
            if ($person) {
                event(new CellMemberAdded(cell: $this->cell, person: $person));
            }
        }

        $this->newMemberId = '';
        session()->flash('success', 'Miembro agregado a la célula.');
    }

    public function removeMember(int $personId): void
    {
        abort_unless(Gate::allows('cells.update'), 403);

        $this->cell->members()->updateExistingPivot($personId, ['left_at' => now()]);
        session()->flash('success', 'Miembro removido de la célula.');
    }

    public function openMultiply(): void
    {
        // TODO: re-enable after running RolePermissionSeeder
        // abort_unless(Gate::allows('cells.multiply'), 403);
        $this->resetValidation();
        $this->reset([
            'newCellName', 'newHostId', 'childAssistantId', 'parentNewAssistantId',
            'newAddressLine1', 'newAddressLine2', 'newCity', 'newState', 'newPostalCode',
            'newMeetingDay', 'newMeetingTime', 'selectedMemberIds',
        ]);
        $this->showMultiplyModal = true;
    }

    public function multiply(): void
    {
        // TODO: re-enable after running RolePermissionSeeder
        // abort_unless(Gate::allows('cells.multiply'), 403);

        $data = $this->validate([
            'newCellName'          => 'required|string|max:255',
            'newHostId'            => 'nullable|exists:people,id',
            'childAssistantId'     => 'nullable|exists:people,id',
            'parentNewAssistantId' => 'nullable|exists:people,id',
            'newAddressLine1'      => 'required|string|max:255',
            'newAddressLine2'      => 'nullable|string|max:255',
            'newCity'              => 'nullable|string|max:100',
            'newState'             => 'nullable|string|max:100',
            'newPostalCode'        => 'nullable|string|max:20',
            'newMeetingDay'        => 'nullable|in:' . implode(',', array_column(DayOfWeek::cases(), 'value')),
            'newMeetingTime'       => 'nullable|date_format:H:i',
            'selectedMemberIds'    => 'required|array|min:1',
            'selectedMemberIds.*'  => 'exists:people,id',
        ]);

        $actionData = [
            'name'                    => $data['newCellName'],
            'host_id'                 => $data['newHostId'] ?: null,
            'child_assistant_id'      => $data['childAssistantId'] ?: null,
            'parent_new_assistant_id' => $data['parentNewAssistantId'] ?: null,
            'address_line1'           => $data['newAddressLine1'],
            'address_line2'           => $data['newAddressLine2'] ?: null,
            'city'                    => $data['newCity'] ?: null,
            'state'                   => $data['newState'] ?: null,
            'postal_code'             => $data['newPostalCode'] ?: null,
            'meeting_day'             => $data['newMeetingDay'] ?: null,
            'meeting_time'            => $data['newMeetingTime'] ?: null,
            'member_ids'              => $data['selectedMemberIds'],
        ];

        $newCell = (new MultiplyCell())->handle($this->cell, $actionData);

        $this->showMultiplyModal = false;
        session()->flash('success', "Célula multiplicada. Nueva célula: {$newCell->name}");

        $this->redirect(route('admin.cells.show', $newCell), navigate: true);
    }

    public function render()
    {
        $this->cell->load(['leader', 'assistant', 'host', 'parentCell', 'childCells.leader']);
        $activeMembers = $this->cell->activeMembers()->orderBy('first_name')->get();
        $people = Person::orderBy('first_name')->get();
        $days = DayOfWeek::cases();
        $memberCount = $activeMembers->count();

        return view('livewire.cells.show', compact('activeMembers', 'people', 'days', 'memberCount'));
    }
}
