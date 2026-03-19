<?php

namespace App\Livewire\Communications;

use App\Models\Communication;
use App\Models\CommunicationRecipient;
use App\Models\Person;
use App\Support\Enums\CommunicationStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CommunicationShow extends Component
{
    use WithPagination;

    public Communication $communication;
    public string $search = '';
    public string $recipientFilter = '';

    // Add person modal
    public bool $showAddModal = false;
    public string $personSearch = '';
    public ?int $personId = null;

    // Bulk add modal
    public bool $showBulkModal = false;
    public string $bulkFilter = 'all';

    public function mount(Communication $communication): void
    {
        $this->communication = $communication;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Add Single Person ──────────────────────────────────

    public function openAddPerson(): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
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
        abort_unless(Gate::allows('communication.send'), 403);
        $this->validate(['personId' => 'required|exists:people,id']);

        if ($this->communication->recipients()->where('person_id', $this->personId)->exists()) {
            $this->addError('personId', 'Esta persona ya está en la lista de destinatarios.');
            return;
        }

        $this->communication->recipients()->create([
            'person_id' => $this->personId,
            'status'    => 'pending',
        ]);

        $this->showAddModal = false;
        $this->reset(['personSearch', 'personId']);
        session()->flash('success', 'Destinatario agregado.');
    }

    // ── Bulk Add ───────────────────────────────────────────

    public function openBulkAdd(): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
        $this->bulkFilter = 'all';
        $this->showBulkModal = true;
    }

    public function bulkAdd(): void
    {
        abort_unless(Gate::allows('communication.send'), 403);

        $query = Person::query();

        match ($this->bulkFilter) {
            'active_member' => $query->where('status', 'active_member'),
            'member'        => $query->whereIn('status', ['member', 'active_member']),
            'visitor'       => $query->where('status', 'visitor'),
            default         => null, // all
        };

        $existingIds = $this->communication->recipients()->pluck('person_id');
        $people = $query->whereNotIn('id', $existingIds)->get();

        $records = $people->map(fn ($p) => [
            'communication_id' => $this->communication->id,
            'person_id'        => $p->id,
            'status'           => 'pending',
            'created_at'       => now(),
            'updated_at'       => now(),
        ])->toArray();

        CommunicationRecipient::insert($records);

        $this->showBulkModal = false;
        session()->flash('success', "{$people->count()} destinatarios agregados.");
    }

    // ── Remove ─────────────────────────────────────────────

    public function removeRecipient(int $recipientId): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
        CommunicationRecipient::findOrFail($recipientId)->delete();
        session()->flash('success', 'Destinatario removido.');
    }

    public function clearAllRecipients(): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
        $this->communication->recipients()->delete();
        session()->flash('success', 'Todos los destinatarios removidos.');
    }

    // ── Send / Schedule ────────────────────────────────────

    public function send(): void
    {
        abort_unless(Gate::allows('communication.send'), 403);

        $comm = $this->communication;

        if ($comm->recipients()->count() === 0) {
            session()->flash('error', 'No hay destinatarios. Agregue personas antes de enviar.');
            return;
        }

        // Mark as sending
        $comm->update(['status' => CommunicationStatus::Sending]);

        $totalSent = 0;
        $totalFailed = 0;

        foreach ($comm->recipients()->with('person')->where('status', 'pending')->get() as $recipient) {
            // Simulate sending — in production, dispatch a job per recipient
            // that calls the actual WhatsApp/Email/SMS provider
            try {
                $recipient->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);
                $totalSent++;
            } catch (\Exception $e) {
                $recipient->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $totalFailed++;
            }
        }

        // Determine final status
        $pendingCount = $comm->recipients()->where('status', 'pending')->count();
        $failedCount = $comm->recipients()->where('status', 'failed')->count();

        if ($pendingCount === 0 && $failedCount === 0) {
            $comm->update(['status' => CommunicationStatus::Sent, 'sent_at' => now()]);
        } elseif ($failedCount > 0) {
            $comm->update(['status' => CommunicationStatus::Partial, 'sent_at' => now()]);
        }

        session()->flash('success', "Enviado a {$totalSent} destinatarios." . ($totalFailed ? " {$totalFailed} fallidos." : ''));
    }

    public function cancel(): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
        $this->communication->update(['status' => CommunicationStatus::Cancelled]);
        session()->flash('success', 'Comunicación cancelada.');
    }

    // ── Render ─────────────────────────────────────────────

    public function render()
    {
        $communication = $this->communication->load('createdBy');

        $recipients = $this->communication->recipients()
            ->with('person')
            ->when($this->search, fn ($q) => $q->whereHas('person', fn ($pq) =>
                $pq->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
            ))
            ->when($this->recipientFilter, fn ($q) => $q->where('status', $this->recipientFilter))
            ->orderBy('created_at')
            ->paginate(30);

        $stats = [
            'total'   => $this->communication->recipients()->count(),
            'pending' => $this->communication->recipients()->where('status', 'pending')->count(),
            'sent'    => $this->communication->recipients()->where('status', 'sent')->count(),
            'failed'  => $this->communication->recipients()->where('status', 'failed')->count(),
        ];

        $isEditable = in_array($communication->status, [CommunicationStatus::Draft, CommunicationStatus::Scheduled]);

        $searchResults = collect();
        if ($this->showAddModal && strlen($this->personSearch) >= 2 && !$this->personId) {
            $searchResults = Person::where(function ($q) {
                $q->where('first_name', 'like', "%{$this->personSearch}%")
                  ->orWhere('last_name', 'like', "%{$this->personSearch}%");
            })->limit(10)->get();
        }

        return view('livewire.communications.show', compact(
            'communication', 'recipients', 'stats', 'isEditable', 'searchResults'
        ));
    }
}
