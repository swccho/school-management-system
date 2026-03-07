<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'code',
        'short_name',
        'type',
        'is_optional',
        'has_practical',
        'full_marks',
        'pass_marks',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_optional' => 'boolean',
            'has_practical' => 'boolean',
            'full_marks' => 'integer',
            'pass_marks' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacherSubjectAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectAssignment::class);
    }

    public function classRoutineItems(): HasMany
    {
        return $this->hasMany(ClassRoutineItem::class);
    }

    public function examSubjectConfigs(): HasMany
    {
        return $this->hasMany(ExamSubjectConfig::class);
    }

    public function markEntries(): HasMany
    {
        return $this->hasMany(MarkEntry::class);
    }

    public function resultSubjectDetails(): HasMany
    {
        return $this->hasMany(ResultSubjectDetail::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
