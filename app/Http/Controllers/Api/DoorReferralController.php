<?php

namespace App\Http\Controllers\Api;

use App\Domain\Doors\Actions\AssignDoorReferral;
use App\Domain\Doors\Actions\ChangeDoorReferralStatus;
use App\Domain\Doors\Actions\CreateDoorReferral;
use App\Domain\Doors\Actions\UpdateDoorReferral;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doors\AssignDoorReferralRequest;
use App\Http\Requests\Doors\ChangeDoorReferralStatusRequest;
use App\Http\Requests\Doors\StoreDoorReferralRequest;
use App\Http\Requests\Doors\UpdateDoorReferralRequest;
use App\Http\Resources\Doors\DoorReferralResource;
use App\Models\DoorReferral;
use App\Support\Enums\DoorReferralStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoorReferralController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('referrals.view');

        $referrals = DoorReferral::query()
            ->with(['door', 'person', 'assignedTo', 'sourceCell'])
            ->when($request->get('door_id'), fn ($q, $v) => $q->where('door_id', $v))
            ->when($request->get('person_id'), fn ($q, $v) => $q->where('person_id', $v))
            ->when($request->get('assigned_to_person_id'), fn ($q, $v) => $q->where('assigned_to_person_id', $v))
            ->when($request->get('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->get('priority'), fn ($q, $v) => $q->where('priority', $v))
            ->when($request->get('source'), fn ($q, $v) => $q->where('source', $v))
            ->when(
                $request->boolean('open_only'),
                fn ($q) => $q->whereIn('status', [
                    DoorReferralStatus::Pending->value,
                    DoorReferralStatus::InProgress->value,
                    DoorReferralStatus::PendingReview->value,
                ]),
            )
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return DoorReferralResource::collection($referrals);
    }

    public function show(DoorReferral $referral): DoorReferralResource
    {
        $this->authorize('referrals.view');

        $referral->load(['door', 'person', 'assignedTo', 'sourceCell', 'sourceUser', 'triggeringRule', 'aiInference']);

        return new DoorReferralResource($referral);
    }

    public function store(StoreDoorReferralRequest $request, CreateDoorReferral $action): DoorReferralResource
    {
        $referral = $action->handle($request->validated());

        return new DoorReferralResource($referral->load(['door', 'person', 'sourceCell', 'sourceUser']));
    }

    public function update(UpdateDoorReferralRequest $request, DoorReferral $referral, UpdateDoorReferral $action): DoorReferralResource
    {
        $referral = $action->handle($referral, $request->validated());

        return new DoorReferralResource($referral->load(['door', 'person', 'assignedTo']));
    }

    public function destroy(DoorReferral $referral): JsonResponse
    {
        $this->authorize('referrals.close');

        $referral->delete();

        return response()->json(['message' => 'Derivación eliminada.']);
    }

    public function assign(AssignDoorReferralRequest $request, DoorReferral $referral, AssignDoorReferral $action): DoorReferralResource
    {
        $referral = $action->handle($referral, $request->validated()['person_id'] ?? null);

        return new DoorReferralResource($referral->load(['door', 'person', 'assignedTo']));
    }

    public function changeStatus(
        ChangeDoorReferralStatusRequest $request,
        DoorReferral $referral,
        ChangeDoorReferralStatus $action,
    ): DoorReferralResource {
        $validated = $request->validated();
        $referral = $action->handle(
            $referral,
            DoorReferralStatus::from($validated['status']),
            $validated['note'] ?? null,
        );

        return new DoorReferralResource($referral->load(['door', 'person', 'assignedTo']));
    }
}
