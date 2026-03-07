<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'school_id',
        'user_id',
        'admission_no',
        'registration_no',
        'roll_no',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'blood_group',
        'religion',
        'photo_path',
        'phone',
        'email',
        'present_address',
        'permanent_address',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(StudentGuardian::class, 'student_guardian_links')
            ->withPivot('relationship_label', 'is_primary', 'can_receive_sms', 'can_receive_email', 'can_login')
            ->withTimestamps()
            ->using(StudentGuardianLink::class);
    }

    public function studentAcademicAssignments(): HasMany
    {
        return $this->hasMany(StudentAcademicAssignment::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function markEntries(): HasMany
    {
        return $this->hasMany(MarkEntry::class);
    }

    public function resultSummaries(): HasMany
    {
        return $this->hasMany(ResultSummary::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
