<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $schoolId = $school->id;

        $firstNames = [
            'Ayan', 'Riya', 'Arjun', 'Sadia', 'Fahim', 'Tahmina', 'Rafiq', 'Nusrat', 'Imran', 'Jahanara',
            'Khalid', 'Laila', 'Omar', 'Priya', 'Quader', 'Reshma', 'Salman', 'Tania', 'Uday', 'Anika',
            'Vikram', 'Bushra', 'Wasim', 'Chandni', 'Yasin',
        ];
        $lastNames = [
            'Rahman', 'Khan', 'Hossain', 'Islam', 'Ahmed', 'Akter', 'Ali', 'Begum', 'Chowdhury', 'Haque',
            'Miah', 'Siddique', 'Uddin', 'Hasan', 'Karim', 'Malik', 'Rahman', 'Khan', 'Hossain', 'Islam',
            'Ahmed', 'Akter', 'Ali', 'Begum', 'Chowdhury',
        ];

        for ($i = 0; $i < 25; $i++) {
            $admissionNo = sprintf('ADM-2024-%03d', $i + 1);
            $firstName = $firstNames[$i];
            $lastName = $lastNames[$i];
            $gender = $i % 2 === 0 ? 'male' : 'female';

            Student::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'admission_no' => $admissionNo,
                ],
                [
                    'user_id' => null,
                    'registration_no' => null,
                    'roll_no' => null,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $gender,
                    'date_of_birth' => Carbon::parse('2015-01-01')->subYears(rand(5, 12)),
                    'blood_group' => null,
                    'religion' => null,
                    'photo_path' => null,
                    'phone' => '01'.rand(500000000, 999999999),
                    'email' => strtolower($firstName.'.'.$lastName).$i.'@student.demoschool.edu',
                    'present_address' => 'Demo Address, Dhaka',
                    'permanent_address' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
