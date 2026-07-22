<?php

namespace App\Http\Controllers\Api;

use App\Domain\Doors\Actions\UpdateDoor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doors\UpdateDoorRequest;
use App\Http\Resources\Doors\DoorResource;
use App\Models\Door;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoorController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('doors.view');

        $doors = Door::query()
            ->withCount(['activeMembers', 'openReferrals', 'unreadAlerts'])
            ->orderBy('order')
            ->get();

        return DoorResource::collection($doors);
    }

    public function show(Door $door): DoorResource
    {
        $this->authorize('doors.view');

        $door->load(['leaders.person', 'rules' => fn ($q) => $q->where('is_enabled', true)])
            ->loadCount(['activeMembers', 'openReferrals', 'unreadAlerts']);

        return new DoorResource($door);
    }

    public function update(UpdateDoorRequest $request, Door $door, UpdateDoor $action): DoorResource
    {
        $door = $action->handle($door, $request->validated());

        return new DoorResource($door);
    }
}
