<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'exam_type_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'result_publish_date',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'result_publish_date' => 'date',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    public function examClassConfigs(): HasMany
    {
        return $this->hasMany(ExamClassConfig::class);
    }

    public function examSubjectConfigs(): HasMany
    {
        return $this->hasMany(ExamSubjectConfig::class);
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
