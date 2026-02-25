<?php

namespace App\Http\Controllers\Api;

use App\Domain\Library\Actions\LoanMaterial;
use App\Domain\Library\Actions\ReturnMaterial;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\LoanMaterialRequest;
use App\Http\Requests\Library\StoreMaterialRequest;
use App\Http\Requests\Library\UpdateMaterialRequest;
use App\Http\Resources\Library\MaterialLoanResource;
use App\Http\Resources\Library\StudyMaterialResource;
use App\Models\MaterialLoan;
use App\Models\Person;
use App\Models\StudyMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudyMaterialController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('library.view');

        $materials = StudyMaterial::query()
            ->when($request->get('search'), fn ($q, $s) =>
                $q->where('title', 'like', "%{$s}%")->orWhere('author', 'like', "%{$s}%")
            )
            ->when($request->get('type'), fn ($q, $t) => $q->where('material_type', $t))
            ->orderBy('title')
            ->paginate($request->get('per_page', 20));

        return StudyMaterialResource::collection($materials);
    }

    public function store(StoreMaterialRequest $request): StudyMaterialResource
    {
        $data = $request->validated();
        $data['available_quantity'] ??= $data['total_quantity'];

        $material = StudyMaterial::create($data);

        return new StudyMaterialResource($material);
    }

    public function show(StudyMaterial $studyMaterial): StudyMaterialResource
    {
        $this->authorize('library.view');

        return new StudyMaterialResource($studyMaterial->load('file'));
    }

    public function update(UpdateMaterialRequest $request, StudyMaterial $studyMaterial): StudyMaterialResource
    {
        $studyMaterial->update($request->validated());

        return new StudyMaterialResource($studyMaterial->fresh());
    }

    public function destroy(StudyMaterial $studyMaterial): JsonResponse
    {
        $this->authorize('library.delete');

        $studyMaterial->delete();

        return response()->json(['message' => 'Material eliminado exitosamente.']);
    }

    public function loans(Request $request, StudyMaterial $studyMaterial): AnonymousResourceCollection
    {
        $this->authorize('library.view');

        $loans = $studyMaterial->loans()
            ->with('person')
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('assigned_at')
            ->paginate($request->get('per_page', 20));

        return MaterialLoanResource::collection($loans);
    }

    public function loan(LoanMaterialRequest $request, StudyMaterial $studyMaterial, LoanMaterial $action): MaterialLoanResource
    {
        $person = Person::findOrFail($request->person_id);
        $loan = $action->handle($studyMaterial, $person, $request->validated());

        return new MaterialLoanResource($loan->load('person', 'studyMaterial'));
    }

    public function returnLoan(StudyMaterial $studyMaterial, MaterialLoan $loan, ReturnMaterial $action): MaterialLoanResource
    {
        $this->authorize('library.return');

        $loan = $action->handle($loan);

        return new MaterialLoanResource($loan->load('person', 'studyMaterial'));
    }
}
