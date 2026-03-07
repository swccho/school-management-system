<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSubjectConfig extends Model
{
    protected $fillable = [
        'school_id',
        'exam_id',
        'class_id',
        'subject_id',
        'full_marks',
        'pass_marks',
        'theory_marks',
        'practical_marks',
        'oral_marks',
        'has_practical',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'full_marks' => 'integer',
            'pass_marks' => 'integer',
            'theory_marks' => 'integer',
            'practical_marks' => 'integer',
            'oral_marks' => 'integer',
            'has_practical' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function markComponents(): HasMany
    {
        return $this->hasMany(MarkComponent::class);
    }
}
