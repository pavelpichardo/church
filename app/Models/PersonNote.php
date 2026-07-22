<?php

namespace App\Models;

use App\Support\Enums\PersonNoteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'author_user_id',
        'type',
        'action_key',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'type' => PersonNoteType::class,
        ];
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
