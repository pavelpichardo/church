<?php

namespace App\Models;

use App\Support\Enums\DoorCode;
use App\Support\Enums\DoorMemberRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Door extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'order',
        'description',
        'color',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'code' => DoorCode::class,
            'order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorMember::class);
    }

    public function activeMembers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->members()->whereNull('left_at');
    }

    public function leaders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->activeMembers()->where('role', DoorMemberRole::Leader->value);
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorActivity::class);
    }

    public function rules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorRule::class);
    }

    public function referrals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorReferral::class);
    }

    public function openReferrals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->referrals()->whereIn('status', ['pending', 'in_progress', 'pending_review']);
    }

    public function alerts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoorAlert::class);
    }

    public function unreadAlerts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->alerts()->whereNull('read_at');
    }
}
