<?php

namespace App\Models;

use App\Support\Enums\DoorActivityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoorActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'door_id',
        'title',
        'description',
        'scheduled_at',
        'location',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => DoorActivityStatus::class,
            'scheduled_at' => 'datetime',
        ];
    }

    public function door(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Door::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorActivityParticipant::class);
    }

    public function attendees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'door_activity_participants')
            ->withPivot('attended', 'notes')
            ->withTimestamps();
    }
}
