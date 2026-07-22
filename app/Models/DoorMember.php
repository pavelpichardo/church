<?php

namespace App\Models;

use App\Support\Enums\DoorMemberRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoorMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'door_id',
        'person_id',
        'role',
        'joined_at',
        'left_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'role' => DoorMemberRole::class,
            'joined_at' => 'date',
            'left_at' => 'date',
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

    public function isActive(): bool
    {
        return $this->left_at === null;
    }
}
