<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResultSummary extends Model
{
    protected $fillable = [
        'school_id',
        'exam_id',
        'student_id',
        'total_marks',
        'obtained_marks',
        'gpa',
        'letter_grade',
        'merit_position',
        'pass_status',
        'published_status',
        'remarks',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_marks' => 'float',
            'obtained_marks' => 'float',
            'gpa' => 'float',
            'merit_position' => 'integer',
            'published_status' => 'boolean',
            'generated_at' => 'datetime',
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function resultSubjectDetails(): HasMany
    {
        return $this->hasMany(ResultSubjectDetail::class)->orderBy('subject_id');
    }

    public function scopePassed($query)
    {
        return $query->where('pass_status', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('pass_status', 'fail');
    }

    public function scopePublished($query)
    {
        return $query->where('published_status', true);
    }
}
