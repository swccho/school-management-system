<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::firstOrCreate(
            ['code' => 'DEMO'],
            [
                'name' => 'Demo School',
                'code' => 'DEMO',
                'email' => 'contact@demoschool.edu',
                'phone' => null,
                'website' => null,
                'established_year' => null,
                'principal_name' => null,
                'slogan' => null,
                'short_name' => 'Demo',
                'address' => null,
                'city' => null,
                'district' => null,
                'country' => null,
                'postal_code' => null,
                'description' => null,
                'logo_path' => null,
                'favicon_path' => null,
                'status' => 'active',
            ]
        );

        SchoolSetting::firstOrCreate(
            ['school_id' => $school->id],
            [
                'default_language' => 'en',
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'default_currency' => 'USD',
                'attendance_mode' => null,
                'result_publish_policy' => null,
                'theme' => null,
                'maintenance_mode' => false,
            ]
        );
    }
}
