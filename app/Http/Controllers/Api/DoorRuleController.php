<?php

namespace App\Http\Controllers\Api;

use App\Domain\Doors\Actions\CreateDoorRule;
use App\Domain\Doors\Actions\DeleteDoorRule;
use App\Domain\Doors\Actions\ToggleDoorRule;
use App\Domain\Doors\Actions\UpdateDoorRule;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doors\StoreDoorRuleRequest;
use App\Http\Requests\Doors\UpdateDoorRuleRequest;
use App\Http\Resources\Doors\DoorRuleResource;
use App\Models\Door;
use App\Models\DoorRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoorRuleController extends Controller
{
    public function index(Door $door): AnonymousResourceCollection
    {
        $this->authorize('door_rules.view');

        $rules = $door->rules()->orderBy('name')->get();

        return DoorRuleResource::collection($rules);
    }

    public function show(Door $door, DoorRule $rule): DoorRuleResource
    {
        $this->authorize('door_rules.view');
        $this->assertRuleBelongs($door, $rule);

        return new DoorRuleResource($rule);
    }

    public function store(StoreDoorRuleRequest $request, Door $door, CreateDoorRule $action): DoorRuleResource
    {
        $rule = $action->handle($door, $request->validated());

        return new DoorRuleResource($rule);
    }

    public function update(UpdateDoorRuleRequest $request, Door $door, DoorRule $rule, UpdateDoorRule $action): DoorRuleResource
    {
        $this->assertRuleBelongs($door, $rule);

        $rule = $action->handle($rule, $request->validated());

        return new DoorRuleResource($rule);
    }

    public function destroy(Door $door, DoorRule $rule, DeleteDoorRule $action): JsonResponse
    {
        $this->authorize('door_rules.manage');
        $this->assertRuleBelongs($door, $rule);

        $action->handle($rule);

        return response()->json(['message' => 'Regla eliminada.']);
    }

    public function toggle(Door $door, DoorRule $rule, ToggleDoorRule $action): DoorRuleResource
    {
        $this->authorize('door_rules.manage');
        $this->assertRuleBelongs($door, $rule);

        $rule = $action->handle($rule);

        return new DoorRuleResource($rule);
    }

    private function assertRuleBelongs(Door $door, DoorRule $rule): void
    {
        abort_unless($rule->door_id === $door->id, 404);
    }
}
