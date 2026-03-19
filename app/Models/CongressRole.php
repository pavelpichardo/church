<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CongressRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'congress_id',
        'name',
        'description',
    ];

    public function congress(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Congress::class);
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CongressAssignment::class);
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CongressRoleTask::class)->orderBy('phase')->orderBy('sort_order');
    }
}
