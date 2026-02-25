<?php

namespace App\Models;

use App\Support\Enums\MaterialType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'material_type',
        'total_quantity',
        'available_quantity',
        'description',
        'file_id',
    ];

    protected function casts(): array
    {
        return [
            'material_type' => MaterialType::class,
            'total_quantity' => 'integer',
            'available_quantity' => 'integer',
        ];
    }

    public function file(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function loans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MaterialLoan::class);
    }

    public function activeLoans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MaterialLoan::class)->whereIn('status', ['borrowed', 'overdue']);
    }
}
