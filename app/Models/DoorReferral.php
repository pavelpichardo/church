<?php

namespace App\Models;

use App\Support\Enums\DoorReferralPriority;
use App\Support\Enums\DoorReferralSource;
use App\Support\Enums\DoorReferralStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'door_id',
        'person_id',
        'source',
        'source_cell_id',
        'source_user_id',
        'triggered_by_rule_id',
        'ai_inference_id',
        'ai_confidence',
        'ai_reasoning',
        'category',
        'priority',
        'status',
        'assigned_to_person_id',
        'notes',
        'due_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'source' => DoorReferralSource::class,
            'priority' => DoorReferralPriority::class,
            'status' => DoorReferralStatus::class,
            'ai_confidence' => 'decimal:2',
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function door(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Door::class);
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function sourceCell(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cell::class, 'source_cell_id');
    }

    public function sourceUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }

    public function triggeringRule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DoorRule::class, 'triggered_by_rule_id');
    }

    public function aiInference(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DoorAiInference::class, 'ai_inference_id');
    }

    public function assignedTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class, 'assigned_to_person_id');
    }

    public function alerts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorAlert::class, 'referral_id');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            DoorReferralStatus::Pending,
            DoorReferralStatus::InProgress,
            DoorReferralStatus::PendingReview,
        ], true);
    }

    public function isAiGenerated(): bool
    {
        return $this->ai_inference_id !== null;
    }
}
