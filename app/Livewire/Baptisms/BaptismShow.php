<?php

namespace App\Livewire\Baptisms;

use App\Models\Baptism;
use App\Models\Certificate;
use App\Models\Person;
use App\Support\Enums\CertificateType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BaptismShow extends Component
{
    public Baptism $baptism;

    // Add person modal
    public bool $showAddModal = false;
    public string $personSearch = '';
    public ?int $personId = null;

    public function mount(Baptism $baptism): void
    {
        $this->baptism = $baptism;
    }

    // ── People Management ──────────────────────────────────

    public function openAddPerson(): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $this->reset(['personSearch', 'personId']);
        $this->showAddModal = true;
    }

    public function selectPerson(int $id): void
    {
        $person = Person::findOrFail($id);
        $this->personId = $id;
        $this->personSearch = "{$person->first_name} {$person->last_name}";
    }

    public function addPerson(): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $this->validate(['personId' => 'required|exists:people,id']);

        if ($this->baptism->people()->where('person_id', $this->personId)->exists()) {
            $this->addError('personId', 'Esta persona ya está en la lista de bautismo.');
            return;
        }

        $this->baptism->people()->attach($this->personId);

        $this->showAddModal = false;
        $this->reset(['personSearch', 'personId']);
        session()->flash('success', 'Persona agregada al bautismo.');
    }

    public function removePerson(int $personId): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);
        $this->baptism->people()->detach($personId);

        // Also remove their certificate if it exists
        Certificate::where('baptism_id', $this->baptism->id)
            ->where('person_id', $personId)
            ->where('type', CertificateType::Baptism)
            ->delete();

        session()->flash('success', 'Persona removida del bautismo.');
    }

    // ── Certificate Generation ─────────────────────────────

    public function generateCertificate(int $personId): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);

        $baptism = $this->baptism->load('pastor');
        $person = Person::findOrFail($personId);

        $pdf = Pdf::loadView('pdf.baptism-certificate', [
            'baptism' => $baptism,
            'person'  => $person,
        ])->setPaper('letter', 'landscape');

        $filename = "certificado-bautismo-{$baptism->id}-{$personId}.pdf";
        $path = "certificates/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        $file = \App\Models\File::create([
            'disk'          => 'public',
            'path'          => $path,
            'original_name' => $filename,
            'mime_type'     => 'application/pdf',
            'size_bytes'    => Storage::disk('public')->size($path),
            'uploaded_by'   => Auth::id(),
        ]);

        Certificate::updateOrCreate(
            [
                'baptism_id' => $baptism->id,
                'person_id'  => $personId,
                'type'       => CertificateType::Baptism,
            ],
            [
                'issued_at' => now(),
                'file_id'   => $file->id,
                'issued_by' => Auth::id(),
            ]
        );

        session()->flash('success', "Certificado generado para {$person->first_name} {$person->last_name}.");
    }

    public function generateAllCertificates(): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);

        $baptism = $this->baptism->load('pastor');
        $people = $baptism->people;

        foreach ($people as $person) {
            $pdf = Pdf::loadView('pdf.baptism-certificate', [
                'baptism' => $baptism,
                'person'  => $person,
            ])->setPaper('letter', 'landscape');

            $filename = "certificado-bautismo-{$baptism->id}-{$person->id}.pdf";
            $path = "certificates/{$filename}";

            Storage::disk('public')->put($path, $pdf->output());

            $file = \App\Models\File::create([
                'disk'          => 'public',
                'path'          => $path,
                'original_name' => $filename,
                'mime_type'     => 'application/pdf',
                'size_bytes'    => Storage::disk('public')->size($path),
                'uploaded_by'   => Auth::id(),
            ]);

            Certificate::updateOrCreate(
                [
                    'baptism_id' => $baptism->id,
                    'person_id'  => $person->id,
                    'type'       => CertificateType::Baptism,
                ],
                [
                    'issued_at' => now(),
                    'file_id'   => $file->id,
                    'issued_by' => Auth::id(),
                ]
            );
        }

        session()->flash('success', "Certificados generados para {$people->count()} personas.");
    }

    // ── Render ─────────────────────────────────────────────

    public function render()
    {
        $baptism = $this->baptism->load(['pastor', 'people', 'certificates.file']);

        // Index certificates by person_id for easy lookup
        $certificatesByPerson = $baptism->certificates
            ->where('type', CertificateType::Baptism)
            ->keyBy('person_id');

        $searchResults = collect();
        if ($this->showAddModal && strlen($this->personSearch) >= 2 && !$this->personId) {
            $searchResults = Person::where(function ($q) {
                $q->where('first_name', 'like', "%{$this->personSearch}%")
                  ->orWhere('last_name', 'like', "%{$this->personSearch}%");
            })->limit(10)->get();
        }

        return view('livewire.baptisms.show', compact('baptism', 'certificatesByPerson', 'searchResults'));
    }
}
