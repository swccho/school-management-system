<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\School;
use App\Models\Staff;
use App\Models\StaffDepartment;
use App\Services\StaffService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $service = app(StaffService::class);
        $schoolId = $school->id;

        $designation = fn (string $name) => Designation::where('school_id', $schoolId)->where('name', $name)->first()?->id;
        $department = fn (string $name) => StaffDepartment::where('school_id', $schoolId)->where('name', $name)->first()?->id;

        $staffList = [
            ['first_name' => 'Md. Karim', 'last_name' => 'Ahmed', 'gender' => 'male', 'designation' => 'Principal', 'dept' => 'Administration', 'employee_type' => 'staff', 'joining' => '2018-01-15'],
            ['first_name' => 'Fatima', 'last_name' => 'Begum', 'gender' => 'female', 'designation' => 'Vice Principal', 'dept' => 'Administration', 'employee_type' => 'staff', 'joining' => '2019-03-01'],
            ['first_name' => 'Ali', 'last_name' => 'Haider', 'gender' => 'male', 'designation' => 'Senior Teacher', 'dept' => 'Teaching (Science)', 'employee_type' => 'teacher', 'joining' => '2020-01-15'],
            ['first_name' => 'Ayesha', 'last_name' => 'Khan', 'gender' => 'female', 'designation' => 'Teacher', 'dept' => 'Teaching (Science)', 'employee_type' => 'teacher', 'joining' => '2020-06-01'],
            ['first_name' => 'Rahim', 'last_name' => 'Uddin', 'gender' => 'male', 'designation' => 'Teacher', 'dept' => 'Teaching (Arts)', 'employee_type' => 'teacher', 'joining' => '2021-01-10'],
            ['first_name' => 'Nargis', 'last_name' => 'Akter', 'gender' => 'female', 'designation' => 'Teacher', 'dept' => 'Teaching (Commerce)', 'employee_type' => 'teacher', 'joining' => '2021-07-01'],
            ['first_name' => 'John', 'last_name' => 'Smith', 'gender' => 'male', 'designation' => 'Teacher', 'dept' => 'Teaching (Science)', 'employee_type' => 'teacher', 'joining' => '2022-01-15'],
            ['first_name' => 'Maria', 'last_name' => 'Islam', 'gender' => 'female', 'designation' => 'Teacher', 'dept' => 'Teaching (Arts)', 'employee_type' => 'teacher', 'joining' => '2022-06-01'],
            ['first_name' => 'Hasan', 'last_name' => 'Mahmud', 'gender' => 'male', 'designation' => 'Accountant', 'dept' => 'Accounts', 'employee_type' => 'staff', 'joining' => '2019-01-01'],
            ['first_name' => 'Rupa', 'last_name' => 'Khatun', 'gender' => 'female', 'designation' => 'Office Assistant', 'dept' => 'Support Staff', 'employee_type' => 'staff', 'joining' => '2020-09-01'],
            ['first_name' => 'Sohel', 'last_name' => 'Rana', 'gender' => 'male', 'designation' => 'Lab Assistant', 'dept' => 'Support Staff', 'employee_type' => 'staff', 'joining' => '2021-03-15'],
        ];

        foreach ($staffList as $data) {
            $email = strtolower($data['first_name'].'.'.$data['last_name']).'@demoschool.edu';
            Staff::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'email' => $email,
                ],
                [
                    'user_id' => null,
                    'employee_id' => $service->generateEmployeeId($schoolId),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'gender' => $data['gender'],
                    'date_of_birth' => Carbon::parse('1985-01-01')->subYears(rand(25, 50)),
                    'blood_group' => null,
                    'religion' => null,
                    'phone' => '01'.rand(500000000, 999999999),
                    'email' => $email,
                    'address' => 'Demo Address, Dhaka',
                    'joining_date' => $data['joining'],
                    'designation_id' => $designation($data['designation']),
                    'department_id' => $department($data['dept']),
                    'employee_type' => $data['employee_type'],
                    'photo_path' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
