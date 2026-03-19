<?php

namespace App\Models;

use App\Support\Enums\CommunicationStatus;
use App\Support\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'channel',
        'scheduled_at',
        'sent_at',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'channel'      => MessageChannel::class,
            'status'       => CommunicationStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at'      => 'datetime',
        ];
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CommunicationRecipient::class);
    }

    public function people(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'communication_recipients')
            ->withPivot(['status', 'sent_at', 'error_message'])
            ->withTimestamps();
    }
}
