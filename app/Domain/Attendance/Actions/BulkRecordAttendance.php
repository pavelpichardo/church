<?php

namespace App\Domain\Attendance\Actions;

use App\Models\Event;
use App\Support\Enums\CheckinMethod;
use Illuminate\Support\Facades\Auth;

class BulkRecordAttendance
{
    public function handle(Event $event, array $personIds, array $data = []): int
    {
        $now = now();
        $recordedBy = $data['recorded_by'] ?? Auth::id();
        $method = $data['checkin_method'] ?? CheckinMethod::Manual->value;

        $existing = \App\Models\AttendanceRecord::where('event_id', $event->id)
            ->whereIn('person_id', $personIds)
            ->pluck('person_id')
            ->toArray();

        $newIds = array_diff($personIds, $existing);

        $records = array_map(fn ($personId) => [
            'event_id' => $event->id,
            'person_id' => $personId,
            'checked_in_at' => $now,
            'checkin_method' => $method,
            'recorded_by' => $recordedBy,
            'notes' => $data['notes'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $newIds);

        if (!empty($records)) {
            \App\Models\AttendanceRecord::insert($records);
        }

        return count($records);
    }
}
