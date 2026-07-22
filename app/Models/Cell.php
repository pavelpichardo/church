<?php

namespace App\Models;

use App\Support\Enums\CellStatus;
use App\Support\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cell extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'leader_id',
        'assistant_id',
        'host_id',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'status',
        'parent_cell_id',
        'max_capacity',
        'meeting_day',
        'meeting_time',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => CellStatus::class,
            'meeting_day' => DayOfWeek::class,
            'max_capacity' => 'integer',
        ];
    }

    public function leader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class, 'leader_id');
    }

    public function assistant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class, 'assistant_id');
    }

    public function host(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class, 'host_id');
    }

    public function parentCell(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cell::class, 'parent_cell_id');
    }

    public function childCells(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Cell::class, 'parent_cell_id');
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'cell_members')
            ->withPivot('joined_at', 'left_at')
            ->withTimestamps();
    }

    public function activeMembers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->members()->whereNull('cell_members.left_at');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }
}
