<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'code',
        'numeric_level',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'numeric_level' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderByRaw('numeric_level IS NULL, numeric_level ASC')
            ->orderBy('name');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }
}
