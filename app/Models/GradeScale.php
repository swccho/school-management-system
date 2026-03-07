<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeScale extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function gradeScaleItems(): HasMany
    {
        return $this->hasMany(GradeScaleItem::class)->orderBy('min_mark');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
