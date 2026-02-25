<?php

namespace App\Models;

use App\Support\Enums\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'event_type',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'is_recurring',
        'recurrence_rule',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => EventType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_recurring' => 'boolean',
        ];
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendanceRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function congress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Congress::class);
    }

    public function baptism(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Baptism::class);
    }

    public function marriage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Marriage::class);
    }
}
