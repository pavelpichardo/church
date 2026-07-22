<?php

namespace App\Livewire\Doors;

use App\Domain\Doors\Actions\ChangeDoorReferralStatus;
use App\Models\Door;
use App\Models\DoorReferral;
use App\Support\Enums\DoorReferralStatus;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class PendingReviewInbox extends Component
{
    use WithPagination;

    #[Url(as: 'door')]
    public string $doorFilter = '';

    public function updatingDoorFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $referralId): void
    {
        abort_unless(Gate::allows('referrals.review_pending'), 403);
        $referral = DoorReferral::findOrFail($referralId);
        (new ChangeDoorReferralStatus())->handle($referral, DoorReferralStatus::Pending, 'Aprobada desde bandeja de revisión');
        session()->flash('success', "Derivación #{$referralId} aprobada.");
    }

    public function reject(int $referralId): void
    {
        abort_unless(Gate::allows('referrals.review_pending'), 403);
        $referral = DoorReferral::findOrFail($referralId);
        (new ChangeDoorReferralStatus())->handle($referral, DoorReferralStatus::Cancelled, 'Rechazada desde bandeja de revisión');
        session()->flash('success', "Derivación #{$referralId} rechazada.");
    }

    public function render()
    {
        $referrals = DoorReferral::query()
            ->where('status', DoorReferralStatus::PendingReview->value)
            ->with(['door', 'person', 'aiInference'])
            ->when($this->doorFilter, fn ($q) => $q->whereHas('door', fn ($d) => $d->where('code', $this->doorFilter)))
            ->orderByDesc('created_at')
            ->paginate(20);

        $doors = Door::orderBy('order')->get();

        return view('livewire.doors.pending-review', compact('referrals', 'doors'));
    }
}
