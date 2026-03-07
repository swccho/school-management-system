<?php

namespace App\Services;

use App\Models\SchoolClass;

class SchoolClassService
{
    private const PREFIX = 'CLS';

    /**
     * Generate a unique class code in the form PREFIX-NNN (e.g. CLS-001, CLS-002).
     *
     * Rules:
     * - If $schoolId is provided, uniqueness is scoped to that school.
     * - If $schoolId is null, uniqueness is checked globally across all classes.
     * - If editing and the current class already has a valid generated code, return it.
     */
    public function generateCode(?string $name, ?int $numericLevel, ?int $excludeId = null, ?int $schoolId = null): string
    {
        if ($excludeId !== null) {
            $schoolClass = SchoolClass::find($excludeId);

            if (
                $schoolClass?->code &&
                preg_match('/^'.preg_quote(self::PREFIX, '/').'-(\d{3})$/', $schoolClass->code)
            ) {
                return $schoolClass->code;
            }
        }

        $query = SchoolClass::query()
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId))
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId));

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
