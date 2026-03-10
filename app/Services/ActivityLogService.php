<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function log(
        string $module,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $description = null,
        array $metadata = [],
        ?Request $request = null
    ): ActivityLog {
        $request = $request ?? request();
        $user = Auth::user();

        return ActivityLog::create([
            'school_id' => $user?->school_id,
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata ?: null,
        ]);
    }

    /**
     * Log an action for a model record. Extracts subject_type and subject_id from the model.
     */
    public function logFor(Model $record, string $action, string $description = null, array $metadata = [], ?Request $request = null): ActivityLog
    {
        $module = $this->modelToModule($record);
        $description = $description ?? $this->defaultDescription($module, $action, $record);

        return $this->log(
            $module,
            $action,
            $record->getMorphClass(),
            $record->getKey(),
            $description,
            $metadata,
            $request
        );
    }

    private function modelToModule(Model $model): string
    {
        $class = class_basename($model);
        return strtolower(preg_replace('/\B([A-Z])/', '-$1', $class));
    }

    private function defaultDescription(string $module, string $action, Model $record): string
    {
        $id = $record->getKey();
        return "{$module} #{$id} {$action}d.";
    }
}
