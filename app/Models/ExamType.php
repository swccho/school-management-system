<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamType extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'status',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'exam_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
