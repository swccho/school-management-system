<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScaleItem extends Model
{
    protected $fillable = [
        'school_id',
        'grade_scale_id',
        'min_mark',
        'max_mark',
        'letter_grade',
        'grade_point',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'min_mark' => 'float',
            'max_mark' => 'float',
            'grade_point' => 'float',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function gradeScale(): BelongsTo
    {
        return $this->belongsTo(GradeScale::class);
    }

    /**
     * Check if a numeric mark falls within this range (inclusive).
     */
    public function containsMark(float $mark): bool
    {
        return $mark >= $this->min_mark && $mark <= $this->max_mark;
    }
}
