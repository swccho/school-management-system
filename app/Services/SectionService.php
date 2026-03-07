<?php

namespace App\Services;

use App\Models\Section;

class SectionService
{
    private const PREFIX = 'SEC';

    /**
     * Generate a unique section code in the form PREFIX-NNN (e.g. SEC-001, SEC-002).
     *
     * Scoped by school_id. When editing (excludeId given), if the section already
     * has a valid generated code, return it unchanged.
     */
    public function generateCode(?int $schoolId, ?int $excludeId = null): string
    {
        if ($excludeId !== null) {
            $section = Section::find($excludeId);

            if (
                $section?->code &&
                preg_match('/^'.preg_quote(self::PREFIX, '/').'-(\d{3})$/', $section->code)
            ) {
                return $section->code;
            }
        }

        $query = Section::query()
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
