<?php

namespace App\Jobs;

use App\Events\BirthdayUpcoming;
use App\Models\DoorAlert;
use App\Models\Person;
use App\Support\Enums\DoorCode;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DetectUpcomingBirthdaysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $today = now();
        $bienvenidaDoorId = \App\Models\Door::where('code', DoorCode::Bienvenida->value)->value('id');

        // Build the set of MM-DD strings we care about: today + next 7 days.
        $upcomingDates = collect(range(0, 7))->map(fn (int $offset) => $today->copy()->addDays($offset)->format('m-d'));

        $candidates = Person::query()
            ->whereNotNull('birth_date')
            ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') IN (" . $upcomingDates->map(fn () => '?')->implode(',') . ')', $upcomingDates->all())
            ->get();

        $emitted = 0;
        foreach ($candidates as $person) {
            $birthMd = Carbon::parse($person->birth_date)->format('m-d');
            $daysUntil = $upcomingDates->search($birthMd);
            if ($daysUntil === false) {
                continue;
            }

            // Debounce: skip if a birthday alert already exists for this person within 14 days
            $recentlyAlerted = DoorAlert::query()
                ->where('door_id', $bienvenidaDoorId)
                ->where('type', 'like', 'ai.cumpleanios%')
                ->whereHas('referral', fn ($q) => $q->where('person_id', $person->id))
                ->where('created_at', '>=', now()->subDays(14))
                ->exists();

            if ($recentlyAlerted) {
                continue;
            }

            event(new BirthdayUpcoming(person: $person, daysUntil: $daysUntil));
            $emitted++;
        }

        Log::info("DetectUpcomingBirthdaysJob: emitted {$emitted} birthday events.");
    }
}
