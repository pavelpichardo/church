<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'communication_id',
        'person_id',
        'status',
        'sent_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function communication(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Communication::class);
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
