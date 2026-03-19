<?php

namespace App\Livewire\Communications;

use App\Models\Communication;
use App\Support\Enums\CommunicationStatus;
use App\Support\Enums\MessageChannel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class CommunicationsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form
    public string $title = '';
    public string $body = '';
    public string $channel = '';
    public string $scheduled_at = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
        $this->reset(['title', 'body', 'channel', 'scheduled_at', 'editingId']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
        $comm = Communication::findOrFail($id);

        if (!in_array($comm->status, [CommunicationStatus::Draft, CommunicationStatus::Scheduled])) {
            session()->flash('error', 'Solo se pueden editar comunicaciones en borrador o programadas.');
            return;
        }

        $this->editingId = $id;
        $this->title = $comm->title;
        $this->body = $comm->body;
        $this->channel = $comm->channel->value;
        $this->scheduled_at = $comm->scheduled_at?->format('Y-m-d\TH:i') ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(Gate::allows('communication.send'), 403);

        $data = $this->validate([
            'title'        => 'required|string|max:200',
            'body'         => 'required|string',
            'channel'      => 'required|in:email,sms,whatsapp',
            'scheduled_at' => 'nullable|date',
        ]);

        $status = $data['scheduled_at'] ? CommunicationStatus::Scheduled : CommunicationStatus::Draft;

        if ($this->editingId) {
            $comm = Communication::findOrFail($this->editingId);
            $comm->update([
                'title'        => $data['title'],
                'body'         => $data['body'],
                'channel'      => $data['channel'],
                'scheduled_at' => $data['scheduled_at'] ?: null,
                'status'       => $status,
            ]);
            session()->flash('success', 'Comunicación actualizada.');
        } else {
            Communication::create([
                'title'        => $data['title'],
                'body'         => $data['body'],
                'channel'      => $data['channel'],
                'scheduled_at' => $data['scheduled_at'] ?: null,
                'status'       => $status,
                'created_by'   => Auth::id(),
            ]);
            session()->flash('success', 'Comunicación creada.');
        }

        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
        Communication::findOrFail($id)->delete();
        session()->flash('success', 'Comunicación eliminada.');
    }

    public function duplicate(int $id): void
    {
        abort_unless(Gate::allows('communication.send'), 403);
        $comm = Communication::with('recipients')->findOrFail($id);

        $new = Communication::create([
            'title'      => $comm->title . ' (copia)',
            'body'       => $comm->body,
            'channel'    => $comm->channel,
            'status'     => CommunicationStatus::Draft,
            'created_by' => Auth::id(),
        ]);

        // Copy recipients
        foreach ($comm->recipients as $r) {
            $new->recipients()->create([
                'person_id' => $r->person_id,
                'status'    => 'pending',
            ]);
        }

        session()->flash('success', 'Comunicación duplicada como borrador.');
    }

    public function render()
    {
        $communications = Communication::withCount('recipients')
            ->with('createdBy')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(20);

        $channels = MessageChannel::cases();
        $statuses = CommunicationStatus::cases();

        return view('livewire.communications.index', compact('communications', 'channels', 'statuses'));
    }
}
