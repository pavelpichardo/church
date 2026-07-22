<?php

namespace App\Http\Controllers\Api;

use App\Domain\Cells\Actions\CreateCell;
use App\Domain\Cells\Actions\MultiplyCell;
use App\Domain\Cells\Actions\UpdateCell;
use App\Events\CellMemberAdded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cells\StoreCellRequest;
use App\Http\Requests\Cells\UpdateCellRequest;
use App\Http\Resources\Cells\CellResource;
use App\Models\Cell;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CellController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('cells.view');

        $cells = Cell::with(['leader', 'assistant', 'host'])
            ->withCount('activeMembers')
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->get('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate($request->get('per_page', 20));

        return CellResource::collection($cells);
    }

    public function store(StoreCellRequest $request, CreateCell $action): CellResource
    {
        $cell = $action->handle($request->validated());

        return new CellResource($cell->load(['leader', 'assistant', 'host']));
    }

    public function show(Cell $cell): CellResource
    {
        $this->authorize('cells.view');

        $cell->load(['leader', 'assistant', 'host', 'parentCell', 'createdBy'])
            ->loadCount(['activeMembers', 'childCells']);

        return new CellResource($cell);
    }

    public function update(UpdateCellRequest $request, Cell $cell, UpdateCell $action): CellResource
    {
        $cell = $action->handle($cell, $request->validated());

        return new CellResource($cell->load(['leader', 'assistant', 'host']));
    }

    public function destroy(Cell $cell): JsonResponse
    {
        $this->authorize('cells.delete');

        $cell->delete();

        return response()->json(['message' => 'Célula eliminada exitosamente.']);
    }

    public function members(Cell $cell): JsonResponse
    {
        $this->authorize('cells.view');

        $members = $cell->activeMembers()->orderBy('first_name')->get()
            ->map(fn (Person $p) => [
                'id' => $p->id,
                'full_name' => $p->full_name,
                'phone' => $p->phone,
                'joined_at' => $p->pivot->joined_at,
            ]);

        return response()->json(['data' => $members]);
    }

    public function addMember(Request $request, Cell $cell): JsonResponse
    {
        $this->authorize('cells.update');

        $request->validate([
            'person_id' => 'required|exists:people,id',
        ]);

        $alreadyActive = $cell->activeMembers()->where('people.id', $request->person_id)->exists();

        $cell->members()->syncWithoutDetaching([
            $request->person_id => ['joined_at' => now()],
        ]);

        if (! $alreadyActive) {
            $person = Person::find($request->person_id);
            if ($person) {
                event(new CellMemberAdded(cell: $cell, person: $person));
            }
        }

        return response()->json(['message' => 'Miembro agregado a la célula.']);
    }

    public function removeMember(Cell $cell, Person $person): JsonResponse
    {
        $this->authorize('cells.update');

        $cell->members()->updateExistingPivot($person->id, ['left_at' => now()]);

        return response()->json(['message' => 'Miembro removido de la célula.']);
    }

    public function multiply(Request $request, Cell $cell, MultiplyCell $action): CellResource
    {
        $this->authorize('cells.multiply');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'host_id' => 'nullable|exists:people,id',
            'child_assistant_id' => 'nullable|exists:people,id',
            'parent_new_assistant_id' => 'nullable|exists:people,id',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'meeting_day' => 'nullable|string',
            'meeting_time' => 'nullable|date_format:H:i',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:people,id',
        ]);

        $newCell = $action->handle($cell, $data);

        return new CellResource($newCell->load(['leader', 'assistant', 'host']));
    }
}
