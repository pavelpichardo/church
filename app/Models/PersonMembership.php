<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'current_stage_id',
        'class_taken_at',
        'class_teacher_id',
        'document_signed_at',
        'document_file_id',
        'pastor_approved_at',
        'pastor_approved_by',
    ];

    protected function casts(): array
    {
        return [
            'class_taken_at' => 'date',
            'document_signed_at' => 'date',
            'pastor_approved_at' => 'datetime',
        ];
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function currentStage(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MembershipStage::class, 'current_stage_id');
    }

    public function classTeacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function documentFile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(File::class, 'document_file_id');
    }

    public function approvedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'pastor_approved_by');
    }
}
