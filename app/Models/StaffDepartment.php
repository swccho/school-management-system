<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffDepartment extends Model
{
    protected $table = 'staff_departments';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'status',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class, 'department_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(Staff::class, 'department_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
