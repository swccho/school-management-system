<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\School;
use App\Services\AcademicSessionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AcademicSessionSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $service = app(AcademicSessionService::class);
        $schoolId = $school->id;

        $sessions = [
            [
                'name' => 'Session 2023-2024',
                'start_date' => Carbon::create(2023, 7, 1),
                'end_date' => Carbon::create(2024, 6, 30),
                'is_current' => false,
            ],
            [
                'name' => 'Session 2024-2025',
                'start_date' => Carbon::create(2024, 7, 1),
                'end_date' => Carbon::create(2025, 6, 30),
                'is_current' => false,
            ],
            [
                'name' => 'Session 2025-2026',
                'start_date' => Carbon::create(2025, 7, 1),
                'end_date' => Carbon::create(2026, 6, 30),
                'is_current' => true,
            ],
        ];

        foreach ($sessions as $index => $data) {
            $code = $service->generateCode(
                $data['name'],
                $data['start_date']->format('Y-m-d'),
                $data['end_date']->format('Y-m-d'),
                null,
                $schoolId
            );
            AcademicSession::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'name' => $data['name'],
                ],
                [
                    'code' => $code,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'is_current' => $data['is_current'],
                    'status' => 'active',
                    'description' => null,
                ]
            );
        }

        AcademicSession::where('school_id', $schoolId)->update(['is_current' => false]);
        AcademicSession::where('school_id', $schoolId)->where('name', 'Session 2025-2026')->update(['is_current' => true]);
    }
}
