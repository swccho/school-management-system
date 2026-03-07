<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRoutine extends Model
{
    protected $fillable = [
        'school_id',
        'academic_session_id',
        'class_id',
        'section_id',
        'title',
        'effective_from',
        'effective_to',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
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

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
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
