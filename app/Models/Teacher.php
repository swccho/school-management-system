<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'school_id',
        'staff_id',
        'teacher_code',
        'qualification',
        'specialization',
        'experience_years',
        'is_class_teacher',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'is_class_teacher' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class, 'teacher_id');
    }

    public function classRoutineItems(): HasMany
    {
        return $this->hasMany(ClassRoutineItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
