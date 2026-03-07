<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PREFIX = 'AY';

    public function up(): void
    {
        $this->fixDuplicateCodes();

        Schema::table('academic_sessions', function (Blueprint $table) {
            $table->unique(['school_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('academic_sessions', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'code']);
        });
    }

    private function fixDuplicateCodes(): void
    {
        $duplicates = DB::table('academic_sessions')
            ->whereNotNull('code')
            ->select('school_id', 'code')
            ->groupBy('school_id', 'code')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $row) {
            $ids = DB::table('academic_sessions')
                ->where('school_id', $row->school_id)
                ->where('code', $row->code)
                ->orderBy('id')
                ->pluck('id');

            $keepId = $ids->first();
            $reassignIds = $ids->slice(1)->values()->all();

            $usedNumbers = $this->getUsedNumbersInScope($row->school_id, $reassignIds);
            $next = $usedNumbers !== [] ? max($usedNumbers) + 1 : 1;

            foreach ($reassignIds as $id) {
                $candidate = self::PREFIX.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
                while ($this->codeExistsInScope($row->school_id, $candidate, $id)) {
                    $next++;
                    $candidate = self::PREFIX.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
                }
                DB::table('academic_sessions')->where('id', $id)->update(['code' => $candidate]);
                $next++;
            }
        }
    }

    private function getUsedNumbersInScope(?int $schoolId, array $excludeIds): array
    {
        $query = DB::table('academic_sessions')
            ->whereNotNull('code')
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId))
            ->when($schoolId === null, fn ($q) => $q->whereNull('school_id'))
            ->whereNotIn('id', $excludeIds);

        $codes = $query->pluck('code')->all();
        $pattern = '/^'.preg_quote(self::PREFIX, '/').'-(\d{3})$/';
        $numbers = [];
        foreach ($codes as $code) {
            if (preg_match($pattern, $code, $m)) {
                $numbers[] = (int) $m[1];
            }
        }
        return $numbers;
    }

    private function codeExistsInScope(?int $schoolId, string $code, int $excludeId): bool
    {
        return DB::table('academic_sessions')
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId))
            ->when($schoolId === null, fn ($q) => $q->whereNull('school_id'))
            ->where('code', $code)
            ->where('id', '!=', $excludeId)
            ->exists();
    }
};
