<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentGuardian extends Model
{
    protected $table = 'student_guardians';

    protected $fillable = [
        'school_id',
        'user_id',
        'name',
        'relation_type',
        'phone',
        'email',
        'occupation',
        'address',
        'photo_path',
        'status',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian_links')
            ->withPivot('relationship_label', 'is_primary', 'can_receive_sms', 'can_receive_email', 'can_login')
            ->withTimestamps()
            ->using(StudentGuardianLink::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
