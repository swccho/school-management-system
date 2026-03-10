<?php

namespace App\Services;

use App\Models\StudentNotification;

class StudentNotificationService
{
    public static function create(int $userId, int $schoolId, string $type, string $title, ?string $body = null, array $data = []): StudentNotification
    {
        return StudentNotification::create([
            'user_id' => $userId,
            'school_id' => $schoolId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}
