<?php

namespace App\Http\Controllers\Api;

use App\Domain\Doors\Actions\AssignDoorMember;
use App\Domain\Doors\Actions\RemoveDoorMember;
use App\Domain\Doors\Actions\UpdateDoorMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doors\StoreDoorMemberRequest;
use App\Http\Requests\Doors\UpdateDoorMemberRequest;
use App\Http\Resources\Doors\DoorMemberResource;
use App\Models\Door;
use App\Models\DoorMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoorMemberController extends Controller
{
    public function index(Request $request, Door $door): AnonymousResourceCollection
    {
        $this->authorize('doors.view');

        $members = $door->members()
            ->with('person')
            ->when($request->boolean('active_only', true), fn ($q) => $q->whereNull('left_at'))
            ->when($request->get('role'), fn ($q, $r) => $q->where('role', $r))
            ->orderBy('role')
            ->get();

        return DoorMemberResource::collection($members);
    }

    public function store(StoreDoorMemberRequest $request, Door $door, AssignDoorMember $action): DoorMemberResource
    {
        $member = $action->handle($door, $request->validated());

        return new DoorMemberResource($member->load('person', 'door'));
    }

    public function update(UpdateDoorMemberRequest $request, Door $door, DoorMember $member, UpdateDoorMember $action): DoorMemberResource
    {
        $this->assertMemberBelongs($door, $member);

        $member = $action->handle($member, $request->validated());

        return new DoorMemberResource($member->load('person', 'door'));
    }

    public function destroy(Door $door, DoorMember $member, RemoveDoorMember $action): JsonResponse
    {
        $this->authorize('door_members.manage');
        $this->assertMemberBelongs($door, $member);

        $action->handle($member);

        return response()->json(['message' => 'Voluntario removido de la puerta.']);
    }

    private function assertMemberBelongs(Door $door, DoorMember $member): void
    {
        abort_unless($member->door_id === $door->id, 404);
    }
}
