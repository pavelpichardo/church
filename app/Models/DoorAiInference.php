<?php

namespace App\Models;

use App\Support\Enums\DoorAiInferenceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorAiInference extends Model
{
    use HasFactory;

    protected $fillable = [
        'triggering_event_type',
        'triggering_event_payload',
        'person_id',
        'model_used',
        'prompt_tokens',
        'cached_tokens',
        'output_tokens',
        'cost_usd',
        'raw_response',
        'decisions',
        'latency_ms',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'triggering_event_payload' => 'array',
            'raw_response' => 'array',
            'decisions' => 'array',
            'status' => DoorAiInferenceStatus::class,
            'prompt_tokens' => 'integer',
            'cached_tokens' => 'integer',
            'output_tokens' => 'integer',
            'latency_ms' => 'integer',
            'cost_usd' => 'decimal:6',
        ];
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function referrals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorReferral::class, 'ai_inference_id');
    }
}
