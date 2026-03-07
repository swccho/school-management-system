<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRoutineItem extends Model
{
    protected $fillable = [
        'school_id',
        'class_routine_id',
        'day_of_week',
        'period_no',
        'start_time',
        'end_time',
        'subject_id',
        'teacher_id',
        'room_label',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'period_no' => 'integer',
        ];
    }

    public const DAYS = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

    public static function validDays(): array
    {
        return self::DAYS;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classRoutine(): BelongsTo
    {
        return $this->belongsTo(ClassRoutine::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
