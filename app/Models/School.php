<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'website',
        'established_year',
        'principal_name',
        'slogan',
        'short_name',
        'address',
        'city',
        'district',
        'country',
        'postal_code',
        'description',
        'logo_path',
        'favicon_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'established_year' => 'integer',
        ];
    }

    public function settings(): HasOne
    {
        return $this->hasOne(SchoolSetting::class);
    }

    public function academicSessions(): HasMany
    {
        return $this->hasMany(AcademicSession::class);
    }

    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function staffDepartments(): HasMany
    {
        return $this->hasMany(StaffDepartment::class, 'school_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(Staff::class, 'school_id');
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'school_id');
    }
}
