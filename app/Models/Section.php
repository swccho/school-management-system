<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    protected $fillable = [
        'school_id',
        'class_id',
        'name',
        'code',
        'room_no',
        'capacity',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->join('school_classes', 'sections.class_id', '=', 'school_classes.id')
            ->orderByRaw('school_classes.numeric_level IS NULL, school_classes.numeric_level ASC')
            ->orderBy('school_classes.name')
            ->orderBy('sections.name')
            ->select('sections.*');
    }
}
