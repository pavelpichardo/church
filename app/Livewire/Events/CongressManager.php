<?php

namespace App\Livewire\Events;

use App\Models\Congress;
use App\Models\CongressAssignment;
use App\Models\CongressRole;
use App\Models\CongressRoleTask;
use App\Models\CongressTaskCompletion;
use App\Models\Event;
use App\Models\Person;
use App\Support\Enums\CongressAssignmentStatus;
use App\Support\Enums\CongressTaskPhase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CongressManager extends Component
{
    public Event $event;
    public Congress $congress;

    // Role modal
    public bool $showRoleModal = false;
    public ?int $editingRoleId = null;
    public string $roleName = '';
    public string $roleDescription = '';

    // Task modal
    public bool $showTaskModal = false;
    public ?int $taskRoleId = null;
    public ?int $editingTaskId = null;
    public string $taskTitle = '';
    public string $taskDescription = '';
    public string $taskPhase = 'before';

    // Assignment modal
    public bool $showAssignModal = false;
    public ?int $assignRoleId = null;
    public string $personSearch = '';
    public ?int $personId = null;

    // Expanded role for viewing details
    public ?int $expandedRoleId = null;

    public function mount(Event $event): void
    {
        $this->event = $event;

        // Auto-create congress record if it doesn't exist
        $this->congress = $event->congress ?? Congress::create(['event_id' => $event->id]);
    }

    // ── Roles ──────────────────────────────────────────────

    public function openCreateRole(): void
    {
        abort_unless(Gate::allows('events.create'), 403);
        $this->reset(['roleName', 'roleDescription', 'editingRoleId']);
        $this->showRoleModal = true;
    }

    public function openEditRole(int $id): void
    {
        abort_unless(Gate::allows('events.update'), 403);
        $role = CongressRole::findOrFail($id);
        $this->editingRoleId = $id;
        $this->roleName = $role->name;
        $this->roleDescription = $role->description ?? '';
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        $data = $this->validate([
            'roleName'        => 'required|string|max:150',
            'roleDescription' => 'nullable|string',
        ]);

        if ($this->editingRoleId) {
            abort_unless(Gate::allows('events.update'), 403);
            CongressRole::findOrFail($this->editingRoleId)->update([
                'name'        => $data['roleName'],
                'description' => $data['roleDescription'] ?: null,
            ]);
            session()->flash('success', 'Rol actualizado.');
        } else {
            abort_unless(Gate::allows('events.create'), 403);
            $this->congress->roles()->create([
                'name'        => $data['roleName'],
                'description' => $data['roleDescription'] ?: null,
            ]);
            session()->flash('success', 'Rol creado.');
        }

        $this->showRoleModal = false;
        $this->reset(['roleName', 'roleDescription', 'editingRoleId']);
    }

    public function deleteRole(int $id): void
    {
        abort_unless(Gate::allows('events.delete'), 403);
        CongressRole::findOrFail($id)->delete();
        if ($this->expandedRoleId === $id) {
            $this->expandedRoleId = null;
        }
        session()->flash('success', 'Rol eliminado.');
    }

    public function toggleRole(int $id): void
    {
        $this->expandedRoleId = $this->expandedRoleId === $id ? null : $id;
    }

    // ── Tasks ──────────────────────────────────────────────

    public function openCreateTask(int $roleId): void
    {
        abort_unless(Gate::allows('events.create'), 403);
        $this->reset(['taskTitle', 'taskDescription', 'editingTaskId']);
        $this->taskPhase = 'before';
        $this->taskRoleId = $roleId;
        $this->showTaskModal = true;
    }

    public function openEditTask(int $taskId): void
    {
        abort_unless(Gate::allows('events.update'), 403);
        $task = CongressRoleTask::findOrFail($taskId);
        $this->editingTaskId = $taskId;
        $this->taskRoleId = $task->congress_role_id;
        $this->taskTitle = $task->title;
        $this->taskDescription = $task->description ?? '';
        $this->taskPhase = $task->phase->value;
        $this->showTaskModal = true;
    }

    public function saveTask(): void
    {
        $data = $this->validate([
            'taskTitle'       => 'required|string|max:200',
            'taskDescription' => 'nullable|string',
            'taskPhase'       => 'required|in:before,during,after',
        ]);

        $attrs = [
            'title'       => $data['taskTitle'],
            'description' => $data['taskDescription'] ?: null,
            'phase'        => $data['taskPhase'],
        ];

        if ($this->editingTaskId) {
            abort_unless(Gate::allows('events.update'), 403);
            CongressRoleTask::findOrFail($this->editingTaskId)->update($attrs);
            session()->flash('success', 'Tarea actualizada.');
        } else {
            abort_unless(Gate::allows('events.create'), 403);
            $maxSort = CongressRoleTask::where('congress_role_id', $this->taskRoleId)->max('sort_order') ?? 0;
            $attrs['sort_order'] = $maxSort + 1;
            CongressRole::findOrFail($this->taskRoleId)->tasks()->create($attrs);
            session()->flash('success', 'Tarea creada.');
        }

        $this->showTaskModal = false;
        $this->reset(['taskTitle', 'taskDescription', 'taskPhase', 'editingTaskId', 'taskRoleId']);
    }

    public function deleteTask(int $taskId): void
    {
        abort_unless(Gate::allows('events.delete'), 403);
        CongressRoleTask::findOrFail($taskId)->delete();
        session()->flash('success', 'Tarea eliminada.');
    }

    // ── Assignments ────────────────────────────────────────

    public function openAssign(int $roleId): void
    {
        abort_unless(Gate::allows('events.create'), 403);
        $this->assignRoleId = $roleId;
        $this->reset(['personSearch', 'personId']);
        $this->showAssignModal = true;
    }

    public function selectPerson(int $id): void
    {
        $person = Person::findOrFail($id);
        $this->personId = $id;
        $this->personSearch = $person->first_name . ' ' . $person->last_name;
    }

    public function assignPerson(): void
    {
        abort_unless(Gate::allows('events.create'), 403);

        $this->validate([
            'personId' => 'required|exists:people,id',
        ]);

        $exists = CongressAssignment::where('congress_role_id', $this->assignRoleId)
            ->where('person_id', $this->personId)
            ->exists();

        if ($exists) {
            $this->addError('personId', 'Esta persona ya está asignada a este rol.');
            return;
        }

        CongressAssignment::create([
            'congress_role_id' => $this->assignRoleId,
            'person_id'        => $this->personId,
            'assigned_by'      => Auth::id(),
            'status'           => CongressAssignmentStatus::Assigned,
        ]);

        $this->showAssignModal = false;
        $this->reset(['personSearch', 'personId', 'assignRoleId']);
        session()->flash('success', 'Persona asignada al rol.');
    }

    public function removeAssignment(int $assignmentId): void
    {
        abort_unless(Gate::allows('events.delete'), 403);
        CongressAssignment::findOrFail($assignmentId)->delete();
        session()->flash('success', 'Asignación eliminada.');
    }

    // ── Task Completions ───────────────────────────────────

    public function toggleTaskCompletion(int $taskId, int $assignmentId): void
    {
        $completion = CongressTaskCompletion::where('congress_role_task_id', $taskId)
            ->where('congress_assignment_id', $assignmentId)
            ->first();

        if ($completion) {
            $completion->delete();
        } else {
            CongressTaskCompletion::create([
                'congress_role_task_id'    => $taskId,
                'congress_assignment_id'   => $assignmentId,
                'completed_at'             => now(),
            ]);
        }
    }

    // ── Render ─────────────────────────────────────────────

    public function render()
    {
        $roles = $this->congress->roles()
            ->withCount(['tasks', 'assignments'])
            ->get();

        $expandedRole = null;
        $roleTasks = collect();
        $roleAssignments = collect();
        $completedTaskIds = collect();

        if ($this->expandedRoleId) {
            $expandedRole = CongressRole::with([
                'tasks' => fn ($q) => $q->orderBy('phase')->orderBy('sort_order'),
                'assignments.person',
                'assignments.taskCompletions',
            ])->find($this->expandedRoleId);

            if ($expandedRole) {
                $roleTasks = $expandedRole->tasks;
                $roleAssignments = $expandedRole->assignments;
                // Build a set of "taskId-assignmentId" for completed
                $completedTaskIds = $expandedRole->assignments
                    ->flatMap(fn ($a) => $a->taskCompletions->map(fn ($c) => $c->congress_role_task_id . '-' . $a->id));
            }
        }

        $searchResults = collect();
        if ($this->showAssignModal && strlen($this->personSearch) >= 2 && !$this->personId) {
            $searchResults = Person::where(function ($q) {
                $q->where('first_name', 'like', "%{$this->personSearch}%")
                  ->orWhere('last_name', 'like', "%{$this->personSearch}%");
            })->limit(10)->get();
        }

        $phases = CongressTaskPhase::cases();

        return view('livewire.events.congress-manager', compact(
            'roles', 'expandedRole', 'roleTasks', 'roleAssignments',
            'completedTaskIds', 'searchResults', 'phases'
        ));
    }
}
