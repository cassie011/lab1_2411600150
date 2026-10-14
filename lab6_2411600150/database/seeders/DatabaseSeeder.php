<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * All sample accounts use the password: password
     */
    public function run(): void
    {
        // Admin
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        // Teacher
        User::factory()->create([
            'name' => 'Teacher Account',
            'email' => 'teacher@example.com',
            'role' => User::ROLE_TEACHER,
        ]);

        // Seed the student records
        $this->call([
            StudentSeeder::class,
        ]);

        // Student account linked to one student record
        $studentUser = User::factory()->create([
            'name' => 'Reahmeil Perocillo',
            'email' => 'student@example.com',
            'role' => User::ROLE_STUDENT,
        ]);

        Student::where('student_id', '2411600006')->update(['user_id' => $studentUser->id]);

        // Sample grades and attendance (Lab 6)
        $this->call([
            AcademicRecordSeeder::class,
        ]);
    }
}
