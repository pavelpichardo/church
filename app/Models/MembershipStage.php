<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function personMemberships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PersonMembership::class, 'current_stage_id');
    }

    public function historyFrom(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MembershipStageHistory::class, 'from_stage_id');
    }

    public function historyTo(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MembershipStageHistory::class, 'to_stage_id');
    }
}
