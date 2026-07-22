<?php

namespace App\Models;

use App\Support\Enums\DoorAlertSeverity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'door_id',
        'referral_id',
        'type',
        'message',
        'severity',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'severity' => DoorAlertSeverity::class,
            'read_at' => 'datetime',
        ];
    }

    public function door(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Door::class);
    }

    public function referral(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DoorReferral::class, 'referral_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markRead(): void
    {
        $this->forceFill(['read_at' => now()])->save();
    }
}
