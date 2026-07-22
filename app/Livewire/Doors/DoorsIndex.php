<?php

namespace App\Livewire\Doors;

use App\Models\Door;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DoorsIndex extends Component
{
    public function render()
    {
        $doors = Door::query()
            ->withCount(['activeMembers', 'openReferrals', 'unreadAlerts'])
            ->with(['leaders.person'])
            ->orderBy('order')
            ->get();

        $totals = [
            'open_referrals' => $doors->sum('open_referrals_count'),
            'pending_review' => \App\Models\DoorReferral::where('status', 'pending_review')->count(),
            'unread_alerts' => $doors->sum('unread_alerts_count'),
        ];

        return view('livewire.doors.index', compact('doors', 'totals'));
    }
}
