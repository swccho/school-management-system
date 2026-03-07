<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarkComponent extends Model
{
    protected $fillable = [
        'school_id',
        'exam_subject_config_id',
        'name',
        'component_type',
        'marks',
        'pass_marks',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'marks' => 'integer',
            'pass_marks' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function examSubjectConfig(): BelongsTo
    {
        return $this->belongsTo(ExamSubjectConfig::class, 'exam_subject_config_id');
    }

    public function markEntryItems(): HasMany
    {
        return $this->hasMany(MarkEntryItem::class);
    }
}
