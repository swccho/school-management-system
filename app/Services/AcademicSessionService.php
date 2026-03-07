<?php

namespace App\Services;

use App\Models\AcademicSession;
use Illuminate\Support\Facades\DB;

class AcademicSessionService
{
    private const PREFIX = 'AY';

    /**
     * Generate a unique academic session code in the form PREFIX-NNN (e.g. AY-001, AY-002).
     *
     * Rules:
     * - If $schoolId is provided, uniqueness is scoped to that school.
     * - If $schoolId is null, uniqueness is checked globally across all sessions.
     * - If editing and the current record already has a valid generated code, return it.
     */
    public function generateCode(?string $name, ?string $startDate, ?string $endDate, ?int $excludeId = null, ?int $schoolId = null): string
    {
        if ($excludeId !== null) {
            $session = AcademicSession::find($excludeId);

            if (
                $session?->code &&
                preg_match('/^'.preg_quote(self::PREFIX, '/').'-(\d{3})$/', $session->code)
            ) {
                return $session->code;
            }
        }

        $query = AcademicSession::query()
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

    public function setCurrent(AcademicSession $session): void
    {
        DB::transaction(function () use ($session) {
            AcademicSession::query()
                ->where('id', '!=', $session->id)
                ->when($session->school_id !== null, fn ($q) => $q->where('school_id', $session->school_id))
                ->when($session->school_id === null, fn ($q) => $q->whereNull('school_id'))
                ->update(['is_current' => false]);

            $session->update(['is_current' => true]);
        });
    }
}
