<?php

namespace App\Services;

use App\Models\TeacherNotification;

class TeacherNotificationService
{
    public static function create(int $userId, int $schoolId, string $type, string $title, ?string $body = null, array $data = []): TeacherNotification
    {
        return TeacherNotification::create([
            'user_id' => $userId,
            'school_id' => $schoolId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}
