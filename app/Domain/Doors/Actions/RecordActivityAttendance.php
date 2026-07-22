<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorActivity;
use App\Models\DoorActivityParticipant;
use Illuminate\Support\Facades\DB;

class RecordActivityAttendance
{
    /**
     * @param  array  $participants  [['person_id' => int, 'attended' => bool, 'notes' => ?string], ...]
     */
    public function handle(DoorActivity $activity, array $participants): DoorActivity
    {
        DB::transaction(function () use ($activity, $participants) {
            foreach ($participants as $p) {
                DoorActivityParticipant::updateOrCreate(
                    ['door_activity_id' => $activity->id, 'person_id' => $p['person_id']],
                    [
                        'attended' => $p['attended'] ?? false,
                        'notes' => $p['notes'] ?? null,
                    ],
                );
            }
        });

        return $activity->fresh()->load('participants.person');
    }
}
