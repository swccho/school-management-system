<?php

namespace App\Services;

use App\Models\Staff;

class StaffService
{
    private const PREFIX = 'EMP';

    /**
     * Generate a unique employee ID in the form PREFIX-NNN (e.g. EMP-001, EMP-002).
     *
     * Uniqueness is scoped by school_id when provided.
     */
    public function generateEmployeeId(?int $schoolId = null): string
    {
        $query = Staff::query()
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId));

        $pattern = '/^'.preg_quote(self::PREFIX, '/').'-(\d{3})$/';

        $maxNumber = (clone $query)
            ->pluck('employee_id')
            ->filter()
            ->map(function ($id) use ($pattern) {
                if (preg_match($pattern, $id, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter()
            ->max();

        $next = $maxNumber ? $maxNumber + 1 : 1;

        $maxAttempts = 1000;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $candidate = self::PREFIX.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);

            $exists = (clone $query)->where('employee_id', $candidate)->exists();

            if (! $exists) {
                return $candidate;
            }

            $next++;
        }

        return self::PREFIX.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
