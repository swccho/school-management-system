<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Centralized activity logging. Records who did what and when.
 * Use for: create, update, delete, login, logout, assign, publish, status changes.
 */
class ActivityLogger
{
    public static function log(
        string $action,
        string $module,
        ?Model $record = null,
        ?string $description = null,
        array $metadata = [],
        ?Request $request = null
    ): \App\Models\ActivityLog {
        $service = app(ActivityLogService::class);

        if ($record !== null) {
            return $service->logFor($record, $action, $description, $metadata, $request);
        }

        $subjectType = null;
        $subjectId = null;
        if (isset($metadata['subject_type'], $metadata['subject_id'])) {
            $subjectType = $metadata['subject_type'];
            $subjectId = $metadata['subject_id'];
            unset($metadata['subject_type'], $metadata['subject_id']);
        }

        return $service->log(
            $module,
            $action,
            $subjectType,
            $subjectId,
            $description,
            $metadata,
            $request
        );
    }
}
