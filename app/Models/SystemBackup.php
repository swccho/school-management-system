<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemBackup extends Model
{
    protected $table = 'system_backups';

    protected $fillable = [
        'school_id',
        'file_name',
        'file_path',
        'file_size',
        'backup_type',
        'status',
        'created_by',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
