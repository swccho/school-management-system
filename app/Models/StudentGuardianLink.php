<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentGuardianLink extends Pivot
{
    protected $table = 'student_guardian_links';

    protected $fillable = [
        'school_id',
        'student_id',
        'guardian_id',
        'relationship_label',
        'is_primary',
        'can_receive_sms',
        'can_receive_email',
        'can_login',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'can_receive_sms' => 'boolean',
            'can_receive_email' => 'boolean',
            'can_login' => 'boolean',
        ];
    }
}
