<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }
}
