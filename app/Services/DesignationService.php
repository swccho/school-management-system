<?php

namespace App\Services;

use App\Models\Designation;

class DesignationService
{
    private const PREFIX = 'DESIG';

    /**
     * Generate a unique designation code in the form PREFIX-NNN (e.g. DESIG-001, DESIG-002).
     *
     * Uniqueness is scoped by school_id when provided.
     */
    public function generateDesignationCode(?int $schoolId = null): string
    {
        $query = Designation::query()
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId));

        $pattern = '/^'.preg_quote(self::PREFIX, '/').'-(\d{3})$/';

        $maxNumber = (clone $query)
            ->pluck('code')
            ->filter()
            ->map(function ($code) use ($pattern) {
                if (preg_match($pattern, $code, $matches)) {
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

            $exists = (clone $query)->where('code', $candidate)->exists();

            if (! $exists) {
                return $candidate;
            }

            $next++;
        }

        return self::PREFIX.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
