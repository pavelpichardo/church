<?php

namespace App\Livewire\Marriages;

use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\Marriage;
use App\Models\Person;
use App\Support\Enums\CertificateType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MarriageShow extends Component
{
    use WithPagination;

    public Marriage $marriage;
    public string $search = '';

    // Quick attendance
    public string $personSearch = '';
    public ?int $personId = null;
    public bool $showAttendanceModal = false;

    public function mount(Marriage $marriage): void
    {
        $this->marriage = $marriage;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Attendance ─────────────────────────────────────────

    public function openAttendanceModal(): void
    {
        abort_unless(Gate::allows('attendance.record'), 403);
        $this->reset(['personSearch', 'personId']);
        $this->showAttendanceModal = true;
    }

    public function selectPerson(int $id): void
    {
        $person = Person::findOrFail($id);
        $this->personId = $id;
        $this->personSearch = "{$person->first_name} {$person->last_name}";
    }

    public function addAttendee(): void
    {
        abort_unless(Gate::allows('attendance.record'), 403);

        $this->validate(['personId' => 'required|exists:people,id']);

        if (!$this->marriage->event_id) {
            $this->addError('personId', 'Este matrimonio no tiene evento asociado.');
            return;
        }

        $exists = AttendanceRecord::where('event_id', $this->marriage->event_id)
            ->where('person_id', $this->personId)
            ->exists();

        if ($exists) {
            $this->addError('personId', 'Esta persona ya está en la lista de asistencia.');
            return;
        }

        AttendanceRecord::create([
            'event_id'    => $this->marriage->event_id,
            'person_id'   => $this->personId,
            'checked_in_at' => now(),
            'checkin_method' => 'manual',
            'recorded_by' => Auth::id(),
        ]);

        $this->showAttendanceModal = false;
        $this->reset(['personSearch', 'personId']);
        session()->flash('success', 'Asistente agregado.');
    }

    public function removeAttendee(int $recordId): void
    {
        abort_unless(Gate::allows('attendance.record'), 403);
        AttendanceRecord::findOrFail($recordId)->delete();
        session()->flash('success', 'Asistente eliminado.');
    }

    // ── Certificate ────────────────────────────────────────

    public function generateCertificate(): void
    {
        abort_unless(Gate::allows('sacraments.create'), 403);

        $marriage = $this->marriage->load(['spouse1', 'spouse2', 'officiant']);

        $pdf = Pdf::loadView('pdf.marriage-certificate', [
            'marriage' => $marriage,
        ])->setPaper('letter', 'landscape');

        $filename = "certificado-matrimonio-{$marriage->id}.pdf";
        $path = "certificates/{$filename}";

        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $pdf->output());

        $file = \App\Models\File::create([
            'disk'          => 'public',
            'path'          => $path,
            'original_name' => $filename,
            'mime_type'     => 'application/pdf',
            'size_bytes'    => \Illuminate\Support\Facades\Storage::disk('public')->size($path),
            'uploaded_by'   => Auth::id(),
        ]);

        // Create or update certificate record
        Certificate::updateOrCreate(
            ['marriage_id' => $marriage->id, 'type' => CertificateType::Marriage],
            [
                'person_id' => $marriage->spouse1_person_id,
                'issued_at' => now(),
                'file_id'   => $file->id,
                'issued_by' => Auth::id(),
            ]
        );

        session()->flash('success', 'Certificado de matrimonio generado.');
    }

    public function downloadCertificate(): mixed
    {
        $certificate = $this->marriage->certificate;
        if (!$certificate?->file) {
            session()->flash('error', 'No hay certificado generado.');
            return null;
        }

        return response()->download(
            \Illuminate\Support\Facades\Storage::disk($certificate->file->disk)->path($certificate->file->path),
            $certificate->file->original_name
        );
    }

    // ── Render ─────────────────────────────────────────────

    public function render()
    {
        $marriage = $this->marriage->load(['spouse1', 'spouse2', 'officiant', 'certificate.file']);

        $attendees = collect();
        $totalAttendees = 0;
        if ($marriage->event_id) {
            $attendees = AttendanceRecord::with('person')
                ->where('event_id', $marriage->event_id)
                ->when($this->search, fn ($q) => $q->whereHas('person', fn ($pq) =>
                    $pq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%")
                ))
                ->orderBy('checked_in_at')
                ->paginate(30);
            $totalAttendees = AttendanceRecord::where('event_id', $marriage->event_id)->count();
        }

        $searchResults = collect();
        if ($this->showAttendanceModal && strlen($this->personSearch) >= 2 && !$this->personId) {
            $searchResults = Person::where(function ($q) {
                $q->where('first_name', 'like', "%{$this->personSearch}%")
                  ->orWhere('last_name', 'like', "%{$this->personSearch}%");
            })->limit(10)->get();
        }

        return view('livewire.marriages.show', compact('marriage', 'attendees', 'totalAttendees', 'searchResults'));
    }
}
