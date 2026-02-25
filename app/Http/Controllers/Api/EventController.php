<?php

namespace App\Http\Controllers\Api;

use App\Domain\Attendance\Actions\BulkRecordAttendance;
use App\Domain\Attendance\Actions\RecordAttendance;
use App\Domain\Events\Actions\CreateEvent;
use App\Domain\Events\Actions\UpdateEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\BulkAttendanceRequest;
use App\Http\Requests\Attendance\RecordAttendanceRequest;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Http\Resources\Attendance\AttendanceRecordResource;
use App\Http\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('events.view');

        $events = Event::withCount('attendanceRecords')
            ->when($request->get('type'), fn ($q, $t) => $q->where('event_type', $t))
            ->when($request->get('from'), fn ($q, $d) => $q->where('starts_at', '>=', $d))
            ->when($request->get('to'), fn ($q, $d) => $q->where('starts_at', '<=', $d))
            ->orderByDesc('starts_at')
            ->paginate($request->get('per_page', 20));

        return EventResource::collection($events);
    }

    public function store(StoreEventRequest $request, CreateEvent $action): EventResource
    {
        $event = $action->handle($request->validated());

        return new EventResource($event);
    }

    public function show(Event $event): EventResource
    {
        $this->authorize('events.view');

        $event->load('createdBy')->loadCount('attendanceRecords');

        return new EventResource($event);
    }

    public function update(UpdateEventRequest $request, Event $event, UpdateEvent $action): EventResource
    {
        $event = $action->handle($event, $request->validated());

        return new EventResource($event);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('events.delete');

        $event->delete();

        return response()->json(['message' => 'Evento eliminado exitosamente.']);
    }

    public function attendance(Request $request, Event $event): AnonymousResourceCollection
    {
        $this->authorize('attendance.view');

        $records = $event->attendanceRecords()
            ->with('person')
            ->orderBy('checked_in_at')
            ->paginate($request->get('per_page', 50));

        return AttendanceRecordResource::collection($records);
    }

    public function recordAttendance(RecordAttendanceRequest $request, Event $event, RecordAttendance $action): AttendanceRecordResource
    {
        $person = Person::findOrFail($request->person_id);
        $record = $action->handle($event, $person, $request->validated());

        return new AttendanceRecordResource($record->load('person'));
    }

    public function bulkAttendance(BulkAttendanceRequest $request, Event $event, BulkRecordAttendance $action): JsonResponse
    {
        $count = $action->handle($event, $request->person_ids, $request->validated());

        return response()->json([
            'message' => "{$count} registros de asistencia creados.",
            'created' => $count,
        ]);
    }
}
