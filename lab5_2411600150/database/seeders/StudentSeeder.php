<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'student_id' => '2411600001',
                'name' => 'Diana Fe Diego',
                'program' => 'BS Computer Science',
                'year_level' => 4,
                'units' => 21,
                'gpa' => 3.75,
                'attendance_rate' => 96
            ],

            [
                'student_id' => '2411600002',
                'name' => 'Renella Del Rosario',
                'program' => 'BS Information Technology',
                'year_level' => 2,
                'units' => 18,
                'gpa' => 1.85,
                'attendance_rate' => 78
            ],

            [
                'student_id' => '2411600003',
                'name' => 'Amron Basher',
                'program' => 'BS Computer Science',
                'year_level' => 4,
                'units' => 24,
                'gpa' => 3.20,
                'attendance_rate' => 91
            ],

            [
                'student_id' => '2411600004',
                'name' => 'Walter Sun',
                'program' => 'BS Accountancy',
                'year_level' => 3,
                'units' => 15,
                'gpa' => 2.40,
                'attendance_rate' => 85
            ],

            [
                'student_id' => '2411600005',
                'name' => 'Wneljae Giangan',
                'program' => 'BS Nursing',
                'year_level' => 3,
                'units' => 22,
                'gpa' => 1.60,
                'attendance_rate' => 70
            ],

            [
                'student_id' => '2411600006',
                'name' => 'Reahmeil Perocillo',
                'program' => 'BS Information Technology',
                'year_level' => 4,
                'units' => 20,
                'gpa' => 3.90,
                'attendance_rate' => 98
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
}