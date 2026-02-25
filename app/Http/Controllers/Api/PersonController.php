<?php

namespace App\Http\Controllers\Api;

use App\Domain\People\Actions\CreatePerson;
use App\Domain\People\Actions\DeletePerson;
use App\Domain\People\Actions\UpdatePerson;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\StorePersonRequest;
use App\Http\Requests\People\UpdatePersonRequest;
use App\Http\Resources\People\PersonResource;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PersonController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('people.view');

        $query = Person::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $people = $query->orderBy('first_name')->orderBy('last_name')
            ->paginate($request->get('per_page', 20));

        return PersonResource::collection($people);
    }

    public function store(StorePersonRequest $request, CreatePerson $action): PersonResource
    {
        $person = $action->handle(array_merge(
            $request->validated(),
            ['created_by' => $request->user()->id]
        ));

        return new PersonResource($person);
    }

    public function show(Person $person): PersonResource
    {
        $this->authorize('people.view');

        $person->load('photo', 'membership.currentStage');

        return new PersonResource($person);
    }

    public function update(UpdatePersonRequest $request, Person $person, UpdatePerson $action): PersonResource
    {
        $person = $action->handle($person, $request->validated());

        return new PersonResource($person);
    }

    public function destroy(Person $person, DeletePerson $action): JsonResponse
    {
        $this->authorize('people.delete');

        $action->handle($person);

        return response()->json(['message' => 'Persona eliminada exitosamente.']);
    }
}
