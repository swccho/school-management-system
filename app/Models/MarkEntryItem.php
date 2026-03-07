<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarkEntryItem extends Model
{
    protected $fillable = [
        'school_id',
        'mark_entry_id',
        'mark_component_id',
        'obtained_marks',
    ];

    protected function casts(): array
    {
        return [
            'obtained_marks' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function markEntry(): BelongsTo
    {
        return $this->belongsTo(MarkEntry::class);
    }

    public function markComponent(): BelongsTo
    {
        return $this->belongsTo(MarkComponent::class, 'mark_component_id');
    }
}
