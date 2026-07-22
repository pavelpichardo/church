<?php

namespace App\Domain\Attendance\Actions;

use App\Events\PersonReturnedAfterAbsence;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Person;
use App\Support\Enums\CheckinMethod;
use Illuminate\Support\Facades\Auth;

class RecordAttendance
{
    public function handle(Event $event, Person $person, array $data = []): AttendanceRecord
    {
        // Capture the most recent prior attendance before recording this one,
        // so we can detect a return after a long absence.
        $previous = AttendanceRecord::where('person_id', $person->id)
            ->latest('checked_in_at')
            ->first();

        $record = AttendanceRecord::firstOrCreate(
            ['event_id' => $event->id, 'person_id' => $person->id],
            [
                'checked_in_at' => $data['checked_in_at'] ?? now(),
                'checkin_method' => $data['checkin_method'] ?? CheckinMethod::Manual,
                'recorded_by' => $data['recorded_by'] ?? Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]
        );

        if ($record->wasRecentlyCreated && $previous) {
            $monthsAbsent = (int) $previous->checked_in_at->diffInMonths($record->checked_in_at);
            if ($monthsAbsent >= 2) {
                event(new PersonReturnedAfterAbsence($person, $monthsAbsent));
            }
        }

        return $record;
    }
}
