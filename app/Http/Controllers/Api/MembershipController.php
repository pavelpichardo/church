<?php

namespace App\Http\Controllers\Api;

use App\Domain\Membership\Actions\AdvanceMembershipStage;
use App\Domain\Membership\Actions\ApproveMembership;
use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\AdvanceStageRequest;
use App\Http\Resources\Membership\PersonMembershipResource;
use App\Models\MembershipStage;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MembershipController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('membership.view');

        $memberships = \App\Models\PersonMembership::with(['person', 'currentStage'])
            ->paginate(20);

        return PersonMembershipResource::collection($memberships);
    }

    public function show(Person $person): PersonMembershipResource
    {
        $this->authorize('membership.view');

        $membership = $person->membership()->with('currentStage')->firstOrFail();

        return new PersonMembershipResource($membership);
    }

    public function advance(AdvanceStageRequest $request, Person $person, AdvanceMembershipStage $action): PersonMembershipResource
    {
        $membership = $action->handle($person, $request->stage_id, $request->note);

        return new PersonMembershipResource($membership);
    }

    public function approve(Person $person, ApproveMembership $action): PersonMembershipResource
    {
        $this->authorize('membership.approve');

        $membership = $action->handle($person);

        return new PersonMembershipResource($membership->load('currentStage'));
    }

    public function stages(): JsonResponse
    {
        $this->authorize('membership.view');

        $stages = MembershipStage::where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'name', 'order']);

        return response()->json($stages);
    }
}
