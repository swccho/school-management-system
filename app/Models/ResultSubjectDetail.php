<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultSubjectDetail extends Model
{
    protected $fillable = [
        'school_id',
        'result_summary_id',
        'subject_id',
        'full_marks',
        'obtained_marks',
        'grade_letter',
        'grade_point',
        'pass_status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'full_marks' => 'float',
            'obtained_marks' => 'float',
            'grade_point' => 'float',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function resultSummary(): BelongsTo
    {
        return $this->belongsTo(ResultSummary::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
