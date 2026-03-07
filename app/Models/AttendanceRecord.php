<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'school_id',
        'attendance_session_id',
        'student_id',
        'student_academic_assignment_id',
        'attendance_status',
        'in_time',
        'out_time',
        'is_late',
        'reason',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'is_late' => 'boolean',
        ];
    }

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_LEAVE = 'leave';

    public static function validStatuses(): array
    {
        return [self::STATUS_PRESENT, self::STATUS_ABSENT, self::STATUS_LATE, self::STATUS_LEAVE];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function studentAcademicAssignment(): BelongsTo
    {
        return $this->belongsTo(StudentAcademicAssignment::class, 'student_academic_assignment_id');
    }
}
