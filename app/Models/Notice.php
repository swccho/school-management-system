<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    protected $fillable = [
        'school_id',
        'title',
        'slug',
        'content',
        'category_id',
        'publish_date',
        'expiry_date',
        'status',
        'is_featured',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'expiry_date' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NoticeCategory::class, 'category_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(NoticeAttachment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        $today = Carbon::today()->toDateString();

        return $query->where('status', 'published')
            ->whereDate('publish_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', $today);
            });
    }
}
