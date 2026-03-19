<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CongressTaskCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'congress_role_task_id',
        'congress_assignment_id',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CongressRoleTask::class, 'congress_role_task_id');
    }

    public function assignment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CongressAssignment::class, 'congress_assignment_id');
    }
}
