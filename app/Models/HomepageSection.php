<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageSection extends Model
{
    protected $fillable = [
        'school_id',
        'section_key',
        'title',
        'subtitle',
        'content',
        'sort_order',
        'is_visible',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
