<?php

namespace App\Models;

use App\Support\Enums\CongressTaskPhase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CongressRoleTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'congress_role_id',
        'title',
        'description',
        'phase',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'phase' => CongressTaskPhase::class,
        ];
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CongressRole::class, 'congress_role_id');
    }

    public function completions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CongressTaskCompletion::class);
    }
}
