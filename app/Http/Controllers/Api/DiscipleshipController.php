<?php

namespace App\Http\Controllers\Api;

use App\Domain\Discipleship\Actions\AssignDiscipleship;
use App\Domain\Discipleship\Actions\CompleteDiscipleship;
use App\Http\Controllers\Controller;
use App\Http\Requests\Discipleship\AssignDiscipleshipRequest;
use App\Http\Requests\Discipleship\StoreDiscipleshipRequest;
use App\Http\Requests\Discipleship\UpdateDiscipleshipRequest;
use App\Http\Resources\Discipleship\DiscipleshipAssignmentResource;
use App\Http\Resources\Discipleship\DiscipleshipResource;
use App\Models\Discipleship;
use App\Models\DiscipleshipAssignment;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DiscipleshipController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('discipleships.view');

        $discipleships = Discipleship::withCount('assignments')
            ->with('leader')
            ->when($request->get('level'), fn ($q, $level) => $q->where('level', $level))
            ->orderBy('name')
            ->paginate($request->get('per_page', 20));

        return DiscipleshipResource::collection($discipleships);
    }

    public function store(StoreDiscipleshipRequest $request): DiscipleshipResource
    {
        $discipleship = Discipleship::create($request->validated());

        return new DiscipleshipResource($discipleship->load('leader'));
    }

    public function show(Discipleship $discipleship): DiscipleshipResource
    {
        $this->authorize('discipleships.view');

        return new DiscipleshipResource($discipleship->load('leader'));
    }

    public function update(UpdateDiscipleshipRequest $request, Discipleship $discipleship): DiscipleshipResource
    {
        $discipleship->update($request->validated());

        return new DiscipleshipResource($discipleship->fresh('leader'));
    }

    public function destroy(Discipleship $discipleship): JsonResponse
    {
        $this->authorize('discipleships.delete');

        $discipleship->delete();

        return response()->json(['message' => 'Discipulado eliminado exitosamente.']);
    }

    public function assignments(Request $request, Discipleship $discipleship): AnonymousResourceCollection
    {
        $this->authorize('discipleships.view');

        $assignments = $discipleship->assignments()
            ->with('person')
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->paginate($request->get('per_page', 20));

        return DiscipleshipAssignmentResource::collection($assignments);
    }

    public function assign(AssignDiscipleshipRequest $request, Discipleship $discipleship, AssignDiscipleship $action): DiscipleshipAssignmentResource
    {
        $person = Person::findOrFail($request->person_id);
        $assignment = $action->handle($discipleship, $person, $request->validated());

        return new DiscipleshipAssignmentResource($assignment->load('person', 'discipleship'));
    }

    public function completeAssignment(Discipleship $discipleship, DiscipleshipAssignment $assignment, CompleteDiscipleship $action): DiscipleshipAssignmentResource
    {
        $this->authorize('discipleships.assign');

        $assignment = $action->handle($assignment);

        return new DiscipleshipAssignmentResource($assignment->load('person'));
    }
}
