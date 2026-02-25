<?php

namespace App\Models;

use App\Support\Enums\Gender;
use App\Support\Enums\HowFoundUs;
use App\Support\Enums\MaritalStatus;
use App\Support\Enums\PersonStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'birth_date',
        'marital_status',
        'gender',
        'how_found_us',
        'first_visit_date',
        'status',
        'notes_pastoral',
        'photo_file_id',
        'user_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'first_visit_date' => 'date',
            'marital_status' => MaritalStatus::class,
            'gender' => Gender::class,
            'how_found_us' => HowFoundUs::class,
            'status' => PersonStatus::class,
        ];
    }

    public function photo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(File::class, 'photo_file_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function membership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PersonMembership::class);
    }

    public function membershipHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MembershipStageHistory::class);
    }

    public function discipleshipAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DiscipleshipAssignment::class);
    }

    public function materialLoans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MaterialLoan::class);
    }

    public function attendanceRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function certificates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function baptisms(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Baptism::class, 'baptism_people');
    }

    public function spouseOneMarriages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Marriage::class, 'spouse1_person_id');
    }

    public function spouseTwoMarriages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Marriage::class, 'spouse2_person_id');
    }

    public function ministries(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Ministry::class, 'ministry_person')
            ->withPivot('joined_at', 'left_at')
            ->withTimestamps();
    }

    public function congressAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CongressAssignment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
