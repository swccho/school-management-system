<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'is_current',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacherSubjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'academic_session_id');
    }

    public function studentAcademicAssignments(): HasMany
    {
        return $this->hasMany(StudentAcademicAssignment::class, 'academic_session_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'academic_session_id');
    }

    public function classRoutines(): HasMany
    {
        return $this->hasMany(ClassRoutine::class, 'academic_session_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'academic_session_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
