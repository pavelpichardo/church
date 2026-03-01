<?php

namespace App\Livewire\Library;

use App\Models\StudyMaterial;
use App\Support\Enums\MaterialType;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MaterialsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $title = '';
    public string $author = '';
    public string $material_type = '';
    public string $total_quantity = '1';
    public string $description = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(Gate::allows('library.create'), 403);
        $this->reset(['title', 'author', 'material_type', 'description', 'editingId']);
        $this->total_quantity = '1';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(Gate::allows('library.update'), 403);
        $m = StudyMaterial::findOrFail($id);
        $this->editingId      = $id;
        $this->title          = $m->title;
        $this->author         = $m->author ?? '';
        $this->material_type  = $m->material_type?->value ?? '';
        $this->total_quantity = (string) $m->total_quantity;
        $this->description    = $m->description ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'title'          => 'required|string|max:200',
            'author'         => 'nullable|string|max:150',
            'material_type'  => 'required|in:' . implode(',', array_column(MaterialType::cases(), 'value')),
            'total_quantity' => 'required|integer|min:1',
            'description'    => 'nullable|string',
        ]);

        if ($this->editingId) {
            $material = StudyMaterial::findOrFail($this->editingId);
            $diff = (int) $data['total_quantity'] - $material->total_quantity;
            $material->update(array_merge($data, [
                'available_quantity' => max(0, $material->available_quantity + $diff),
            ]));
            session()->flash('success', 'Material actualizado.');
        } else {
            StudyMaterial::create(array_merge($data, [
                'available_quantity' => (int) $data['total_quantity'],
            ]));
            session()->flash('success', 'Material creado.');
        }

        $this->showModal = false;
        $this->reset(['title', 'author', 'material_type', 'description', 'editingId']);
        $this->total_quantity = '1';
    }

    public function delete(int $id): void
    {
        abort_unless(Gate::allows('library.delete'), 403);
        StudyMaterial::findOrFail($id)->delete();
        session()->flash('success', 'Material eliminado.');
    }

    public function render()
    {
        $materials = StudyMaterial::withCount(['loans as active_loans_count' => function ($q) {
            $q->whereIn('status', ['borrowed', 'overdue']);
        }])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhere('author', 'like', "%{$this->search}%"))
            ->orderBy('title')
            ->paginate(20);

        $types = MaterialType::cases();

        return view('livewire.library.materials-index', compact('materials', 'types'));
    }
}
