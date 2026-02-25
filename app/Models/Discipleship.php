<?php

namespace App\Models;

use App\Support\Enums\DiscipleshipLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discipleship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'duration_weeks',
        'leader_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'level' => DiscipleshipLevel::class,
            'duration_weeks' => 'integer',
        ];
    }

    public function leader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DiscipleshipAssignment::class);
    }
}
