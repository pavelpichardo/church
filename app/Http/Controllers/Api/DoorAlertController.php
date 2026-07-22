<?php

namespace App\Http\Controllers\Api;

use App\Domain\Doors\Actions\MarkDoorAlertRead;
use App\Http\Controllers\Controller;
use App\Http\Resources\Doors\DoorAlertResource;
use App\Models\Door;
use App\Models\DoorAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoorAlertController extends Controller
{
    public function index(Request $request, Door $door): AnonymousResourceCollection
    {
        $this->authorize('door_alerts.view');

        $alerts = $door->alerts()
            ->with('referral')
            ->when($request->boolean('unread_only'), fn ($q) => $q->whereNull('read_at'))
            ->when($request->get('severity'), fn ($q, $s) => $q->where('severity', $s))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 30));

        return DoorAlertResource::collection($alerts);
    }

    public function markRead(Door $door, DoorAlert $alert, MarkDoorAlertRead $action): DoorAlertResource
    {
        $this->authorize('door_alerts.manage');
        abort_unless($alert->door_id === $door->id, 404);

        $alert = $action->handle($alert);

        return new DoorAlertResource($alert);
    }

    public function markAllRead(Door $door): JsonResponse
    {
        $this->authorize('door_alerts.manage');

        $updated = $door->alerts()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Alertas marcadas como leídas.',
            'updated_count' => $updated,
        ]);
    }
}
