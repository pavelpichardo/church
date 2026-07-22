<?php

namespace App\Models;

use App\Support\Enums\DoorReferralPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'door_id',
        'name',
        'description',
        'event_types',
        'priority_hint',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'event_types' => 'array',
            'priority_hint' => DoorReferralPriority::class,
            'is_enabled' => 'boolean',
        ];
    }

    public function door(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Door::class);
    }

    public function triggeredReferrals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorReferral::class, 'triggered_by_rule_id');
    }

    public function appliesToEvent(string $eventType): bool
    {
        if (empty($this->event_types)) {
            return true;
        }

        return in_array($eventType, $this->event_types, true);
    }
}
