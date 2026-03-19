<?php

namespace App\Models;

use App\Support\Enums\CongressAssignmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CongressAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'congress_role_id',
        'person_id',
        'assigned_by',
        'tasks',
        'confirmed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'status' => CongressAssignmentStatus::class,
        ];
    }

    public function congressRole(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CongressRole::class);
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function assignedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function taskCompletions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CongressTaskCompletion::class);
    }
}
