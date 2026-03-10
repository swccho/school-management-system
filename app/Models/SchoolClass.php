<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'numeric_level',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'numeric_level' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderByRaw('numeric_level IS NULL, numeric_level ASC')
            ->orderBy('name');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function teacherSubjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'class_id');
    }

    public function studentAcademicAssignments(): HasMany
    {
        return $this->hasMany(StudentAcademicAssignment::class, 'class_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'class_id');
    }

    public function classRoutines(): HasMany
    {
        return $this->hasMany(ClassRoutine::class, 'class_id');
    }

    public function examClassConfigs(): HasMany
    {
        return $this->hasMany(ExamClassConfig::class, 'class_id');
    }

    public function examSubjectConfigs(): HasMany
    {
        return $this->hasMany(ExamSubjectConfig::class, 'class_id');
    }

    public function markEntries(): HasMany
    {
        return $this->hasMany(MarkEntry::class, 'class_id');
    }
}
