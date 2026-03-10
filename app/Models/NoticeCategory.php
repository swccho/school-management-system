<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NoticeCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
