<?php

namespace App\Domain\Attendance\Actions;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Person;
use App\Support\Enums\CheckinMethod;
use Illuminate\Support\Facades\Auth;

class RecordAttendance
{
    public function handle(Event $event, Person $person, array $data = []): AttendanceRecord
    {
        return AttendanceRecord::firstOrCreate(
            ['event_id' => $event->id, 'person_id' => $person->id],
            [
                'checked_in_at' => $data['checked_in_at'] ?? now(),
                'checkin_method' => $data['checkin_method'] ?? CheckinMethod::Manual,
                'recorded_by' => $data['recorded_by'] ?? Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]
        );
    }
}
