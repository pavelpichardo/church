<?php

namespace App\Http\Controllers\Api;

use App\Domain\Doors\Actions\CreateDoorActivity;
use App\Domain\Doors\Actions\DeleteDoorActivity;
use App\Domain\Doors\Actions\RecordActivityAttendance;
use App\Domain\Doors\Actions\UpdateDoorActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doors\RecordActivityAttendanceRequest;
use App\Http\Requests\Doors\StoreDoorActivityRequest;
use App\Http\Requests\Doors\UpdateDoorActivityRequest;
use App\Http\Resources\Doors\DoorActivityResource;
use App\Models\Door;
use App\Models\DoorActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoorActivityController extends Controller
{
    public function index(Request $request, Door $door): AnonymousResourceCollection
    {
        $this->authorize('door_activities.view');

        $activities = $door->activities()
            ->withCount('participants')
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('scheduled_at')
            ->paginate($request->get('per_page', 20));

        return DoorActivityResource::collection($activities);
    }

    public function show(Door $door, DoorActivity $activity): DoorActivityResource
    {
        $this->authorize('door_activities.view');
        $this->assertActivityBelongs($door, $activity);

        $activity->load(['participants.person', 'createdBy']);

        return new DoorActivityResource($activity);
    }

    public function store(StoreDoorActivityRequest $request, Door $door, CreateDoorActivity $action): DoorActivityResource
    {
        $activity = $action->handle($door, $request->validated());

        return new DoorActivityResource($activity->load('createdBy'));
    }

    public function update(UpdateDoorActivityRequest $request, Door $door, DoorActivity $activity, UpdateDoorActivity $action): DoorActivityResource
    {
        $this->assertActivityBelongs($door, $activity);

        $activity = $action->handle($activity, $request->validated());

        return new DoorActivityResource($activity);
    }

    public function destroy(Door $door, DoorActivity $activity, DeleteDoorActivity $action): JsonResponse
    {
        $this->authorize('door_activities.manage');
        $this->assertActivityBelongs($door, $activity);

        $action->handle($activity);

        return response()->json(['message' => 'Actividad eliminada.']);
    }

    public function recordAttendance(
        RecordActivityAttendanceRequest $request,
        Door $door,
        DoorActivity $activity,
        RecordActivityAttendance $action,
    ): DoorActivityResource {
        $this->assertActivityBelongs($door, $activity);

        $activity = $action->handle($activity, $request->validated()['participants']);

        return new DoorActivityResource($activity);
    }

    private function assertActivityBelongs(Door $door, DoorActivity $activity): void
    {
        abort_unless($activity->door_id === $door->id, 404);
    }
}
