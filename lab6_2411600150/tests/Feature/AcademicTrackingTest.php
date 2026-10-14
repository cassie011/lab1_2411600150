<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use App\Notifications\StudentAtRiskNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Lab 6 - Part 10: Testing and Verification
 * Run with: php artisan test --filter=AcademicTrackingTest
 */
class AcademicTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function teacher(): User
    {
        return User::factory()->create(['role' => User::ROLE_TEACHER]);
    }

    private function gradeData(Student $student, array $overrides = []): array
    {
        return array_merge([
            'student_id' => $student->id,
            'subject' => 'Programming 2',
            'units' => 3,
            'term' => 'prelim',
            'grade' => '3.50',
        ], $overrides);
    }

    public function test_recording_grades_recalculates_gpa(): void
    {
        $teacher = $this->teacher();
        $student = Student::factory()->create(['gpa' => 0]);

        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['grade' => '4.00', 'units' => 3]))
            ->assertRedirect(route('grades.index'));
        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['subject' => 'PE', 'grade' => '2.00', 'units' => 1]));

        // (4.00 x 3 + 2.00 x 1) / 4 units = 3.50
        $this->assertEquals(3.50, (float) $student->fresh()->gpa);
        $this->assertDatabaseHas('grades', ['student_id' => $student->id, 'user_id' => $teacher->id, 'subject' => 'Programming 2']);
    }

    public function test_correcting_a_grade_recalculates_gpa(): void
    {
        $teacher = $this->teacher();
        $student = Student::factory()->create();

        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['grade' => '3.00']));
        $grade = Grade::first();

        $this->actingAs($teacher)->put(route('grades.update', $grade), $this->gradeData($student, ['grade' => '3.80']))
            ->assertRedirect(route('grades.show', $grade));

        $this->assertEquals(3.80, (float) $student->fresh()->gpa);
    }

    public function test_duplicate_grade_for_same_subject_and_term_is_rejected(): void
    {
        $teacher = $this->teacher();
        $student = Student::factory()->create();

        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student));
        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['grade' => '1.00']))
            ->assertSessionHasErrors('subject');

        $this->assertSame(1, Grade::count());
    }

    public function test_grade_validation_rules(): void
    {
        $teacher = $this->teacher();
        $student = Student::factory()->create();

        $this->actingAs($teacher)->post(route('grades.store'), [])
            ->assertSessionHasErrors(['student_id', 'subject', 'units', 'term', 'grade']);

        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['grade' => '4.50']))
            ->assertSessionHasErrors('grade');

        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['term' => 'summer']))
            ->assertSessionHasErrors('term');

        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['student_id' => 9999]))
            ->assertSessionHasErrors('student_id');

        $this->assertSame(0, Grade::count());
    }

    public function test_observer_and_transaction_roll_back_invalid_grade(): void
    {
        $student = Student::factory()->create(['gpa' => 3.50]);

        try {
            DB::transaction(fn () => Grade::create($this->gradeData($student, ['grade' => 9])));
            $this->fail('Exception was not thrown.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // expected
        }

        $this->assertSame(0, Grade::count());
        $this->assertEquals(3.50, (float) $student->fresh()->gpa);
    }

    public function test_taking_attendance_updates_attendance_rate(): void
    {
        $teacher = $this->teacher();
        $a = Student::factory()->create(['program' => 'BSIT', 'attendance_rate' => 100]);
        $b = Student::factory()->create(['program' => 'BSIT', 'attendance_rate' => 100]);

        $days = [now()->subDays(3), now()->subDays(2), now()->subDay(), now()];
        $statusesA = ['present', 'late', 'absent', 'excused'];

        foreach ($days as $i => $day) {
            $this->actingAs($teacher)->post(route('attendance.store'), [
                'date' => $day->toDateString(),
                'program' => 'BSIT',
                'attendance' => [
                    $a->id => ['status' => $statusesA[$i]],
                    $b->id => ['status' => 'present'],
                ],
            ])->assertRedirect();
        }

        // A: present + late = 2 of 3 counted days (excused not counted) = 67%
        $this->assertSame(67, (int) $a->fresh()->attendance_rate);
        $this->assertSame(100, (int) $b->fresh()->attendance_rate);
        $this->assertSame(8, AttendanceRecord::count());
    }

    public function test_retaking_attendance_on_same_day_updates_instead_of_duplicating(): void
    {
        $teacher = $this->teacher();
        $student = Student::factory()->create(['program' => 'BSIT']);
        $date = now()->toDateString();

        $this->actingAs($teacher)->post(route('attendance.store'), [
            'date' => $date, 'program' => 'BSIT', 'attendance' => [$student->id => ['status' => 'absent']],
        ]);
        $this->assertSame(0, (int) $student->fresh()->attendance_rate);

        $this->actingAs($teacher)->post(route('attendance.store'), [
            'date' => $date, 'program' => 'BSIT', 'attendance' => [$student->id => ['status' => 'present']],
        ]);

        $this->assertSame(1, AttendanceRecord::count());
        $this->assertSame(100, (int) $student->fresh()->attendance_rate);
    }

    public function test_attendance_for_future_date_is_rejected(): void
    {
        $student = Student::factory()->create(['program' => 'BSIT']);

        $this->actingAs($this->teacher())->post(route('attendance.store'), [
            'date' => now()->addDay()->toDateString(),
            'program' => 'BSIT',
            'attendance' => [$student->id => ['status' => 'present']],
        ])->assertSessionHasErrors('date');

        $this->assertSame(0, AttendanceRecord::count());
    }

    public function test_academic_alert_is_sent_once_to_teachers_and_student(): void
    {
        $admin = $this->admin();
        $teacher = $this->teacher();
        $studentUser = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $student = Student::factory()->create(['user_id' => $studentUser->id, 'gpa' => 3.5]);

        // Low grade -> GPA 2.00 -> alert
        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['grade' => '2.00']));

        foreach ([$admin, $teacher, $studentUser] as $user) {
            $this->assertSame(1, $user->fresh()->unreadNotifications()->count());
        }
        $this->assertSame(StudentAtRiskNotification::class, $teacher->notifications()->first()->type);
        $this->assertSame($student->id, $teacher->notifications()->first()->data['student_id']);

        // Another low grade while already at risk: no duplicate
        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['subject' => 'Math', 'grade' => '1.50']));
        $this->assertSame(1, $teacher->fresh()->notifications()->count());

        // Recover above 3.00, then drop again: new alert
        $this->actingAs($teacher)->put(route('grades.update', Grade::where('subject', 'Math')->first()),
            $this->gradeData($student, ['subject' => 'Math', 'grade' => '4.00']));
        $this->actingAs($teacher)->put(route('grades.update', Grade::where('subject', 'Programming 2')->first()),
            $this->gradeData($student, ['grade' => '4.00']));
        $this->assertNull($student->fresh()->alert_notified_at);

        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['subject' => 'Science', 'units' => 6, 'grade' => '0.50']));
        $this->assertSame(2, $teacher->fresh()->notifications()->count());
    }

    public function test_low_attendance_triggers_alert_and_mail_is_queued(): void
    {
        Notification::fake();

        $teacher = $this->teacher();
        $student = Student::factory()->create(['program' => 'BSIT', 'gpa' => 3.8]);

        $this->actingAs($teacher)->post(route('attendance.store'), [
            'date' => now()->toDateString(), 'program' => 'BSIT',
            'attendance' => [$student->id => ['status' => 'absent']],
        ]);

        Notification::assertSentTo($teacher, StudentAtRiskNotification::class, function ($notification, $channels) {
            return in_array('database', $channels) && in_array('mail', $channels);
        });
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $teacher = $this->teacher();
        $student = Student::factory()->create();

        $this->actingAs($teacher)->post(route('grades.store'), $this->gradeData($student, ['grade' => '1.00']));
        $notification = $teacher->fresh()->unreadNotifications()->first();

        $this->actingAs($teacher)->get(route('notifications.read', $notification->id))
            ->assertRedirect(route('students.show', $student->id));

        $this->assertSame(0, $teacher->fresh()->unreadNotifications()->count());
    }

    public function test_dashboard_api_returns_live_statistics(): void
    {
        $teacher = $this->teacher();
        Student::factory()->create(['gpa' => 3.5, 'attendance_rate' => 95]);
        Student::factory()->create(['gpa' => 2.5, 'attendance_rate' => 90]);
        Student::factory()->create(['gpa' => 3.5, 'attendance_rate' => 60]);

        $this->actingAs($teacher)->getJson(route('api.dashboard.stats'))
            ->assertOk()
            ->assertJsonPath('data.total_students', 3)
            ->assertJsonPath('data.at_risk_count', 1)
            ->assertJsonPath('data.needs_attention_count', 2)
            ->assertJsonStructure(['data' => ['average_gpa', 'present_today', 'absent_today', 'grades_today'], 'generated_at']);

        $this->actingAs($teacher)->getJson(route('api.students.atRisk'))
            ->assertOk()->assertJsonCount(2, 'data');

        $this->actingAs($teacher)->getJson(route('api.activity.recent'))
            ->assertOk()->assertJsonStructure(['data', 'generated_at']);
    }

    public function test_api_requires_login_and_staff_role(): void
    {
        $this->getJson(route('api.dashboard.stats'))->assertUnauthorized();

        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $this->actingAs($student)->getJson(route('api.dashboard.stats'))->assertForbidden();
    }

    public function test_dashboard_shows_live_section_and_lab5_content(): void
    {
        $this->actingAs($this->teacher())->get(route('dashboard'))
            ->assertOk()
            ->assertSee("Today's Activity", false)
            ->assertSee('id="statAverageGpa"', false)
            ->assertSee('id="lastUpdated"', false)
            ->assertSee('js/academic-dashboard.js', false)
            ->assertSee('Students List');
    }

    public function test_student_sees_only_own_records(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $mine = Student::factory()->create(['user_id' => $user->id, 'name' => 'My Own Name']);
        $other = Student::factory()->create(['name' => 'Someone Else']);

        Grade::create($this->gradeData($mine, ['subject' => 'My Subject']));
        Grade::create($this->gradeData($other, ['subject' => 'Other Subject']));

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Grades')
            ->assertSee('My Subject')
            ->assertDontSee('Other Subject')
            ->assertDontSee('Someone Else');

        foreach ([route('students.index'), route('students.show', $other), route('grades.index'), route('attendance.create'), route('reports.index')] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_teacher_cannot_use_admin_only_features(): void
    {
        $teacher = $this->teacher();
        $student = Student::factory()->create();
        $grade = Grade::create($this->gradeData($student));
        $record = AttendanceRecord::create(['student_id' => $student->id, 'date' => now()->toDateString(), 'status' => 'present']);

        $this->actingAs($teacher)->delete(route('grades.destroy', $grade))->assertForbidden();
        $this->actingAs($teacher)->delete(route('attendance.destroy', $record))->assertForbidden();

        $this->assertDatabaseHas('grades', ['id' => $grade->id]);
        $this->assertDatabaseHas('attendance_records', ['id' => $record->id]);
    }

    public function test_admin_delete_recalculates(): void
    {
        $admin = $this->admin();
        $student = Student::factory()->create();
        Grade::create($this->gradeData($student, ['grade' => '4.00']));
        $low = Grade::create($this->gradeData($student, ['subject' => 'Math', 'grade' => '2.00']));
        $this->assertEquals(3.00, (float) $student->fresh()->gpa);

        $this->actingAs($admin)->delete(route('grades.destroy', $low))->assertRedirect(route('grades.index'));

        $this->assertEquals(4.00, (float) $student->fresh()->gpa);
    }

    public function test_student_page_shows_running_attendance_rate(): void
    {
        $teacher = $this->teacher();
        $student = Student::factory()->create();

        AttendanceRecord::create(['student_id' => $student->id, 'date' => now()->subDays(2)->toDateString(), 'status' => 'present']);
        AttendanceRecord::create(['student_id' => $student->id, 'date' => now()->subDay()->toDateString(), 'status' => 'absent']);

        $response = $this->actingAs($teacher)->get(route('students.show', $student))
            ->assertOk()
            ->assertSee('Running Rate');

        $history = $response->viewData('history')->values();
        $this->assertSame(50.0, (float) $history[0]->running_rate);  // newest first
        $this->assertSame(100.0, (float) $history[1]->running_rate);

        $this->actingAs($teacher)->get(route('students.show', [$student, 'date_from' => '2000-01-01', 'date_to' => '2000-01-02']))
            ->assertSee('No attendance records for this period.');
    }

    public function test_register_with_roles_and_student_link(): void
    {
        $student = Student::factory()->create(['student_id' => '2411609999']);

        $this->post('/register', [
            'name' => 'New Student', 'email' => 's@example.com', 'role' => 'student',
            'student_number' => '2411609999', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 's@example.com')->first();
        $this->assertSame('student', $user->role);
        $this->assertSame($user->id, $student->fresh()->user_id);

        auth()->logout();

        // Same Student ID cannot be used twice
        $this->post('/register', [
            'name' => 'Copy', 'email' => 'copy@example.com', 'role' => 'student',
            'student_number' => '2411609999', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertSessionHasErrors('student_number');

        // Student role needs a Student ID
        $this->post('/register', [
            'name' => 'No ID', 'email' => 'noid@example.com', 'role' => 'student',
            'password' => 'password', 'password_confirmation' => 'password',
        ])->assertSessionHasErrors('student_number');

        $this->post('/register', [
            'name' => 'New Teacher', 'email' => 't@example.com', 'role' => 'teacher',
            'password' => 'password', 'password_confirmation' => 'password',
        ]);
        $this->assertSame('teacher', User::where('email', 't@example.com')->value('role'));
    }

    public function test_all_pages_render(): void
    {
        $admin = $this->admin();
        $student = Student::factory()->create(['program' => 'BSIT', 'year_level' => 3]);
        $grade = Grade::create($this->gradeData($student) + ['user_id' => $admin->id]);
        AttendanceRecord::create(['student_id' => $student->id, 'user_id' => $admin->id, 'date' => now()->toDateString(), 'status' => 'late']);

        $this->actingAs($admin);

        foreach ([
            route('students.index'),
            route('students.show', $student),
            route('grades.index'),
            route('grades.index', ['term' => 'prelim', 'result' => 'failed', 'program' => 'BSIT']),
            route('grades.create', ['student_id' => $student->id]),
            route('grades.show', $grade),
            route('grades.edit', $grade),
            route('attendance.index'),
            route('attendance.index', ['status' => 'late', 'program' => 'BSIT']),
            route('attendance.create'),
            route('attendance.create', ['program' => 'BSIT', 'year_level' => 3]),
            route('reports.index'),
            route('reports.index', ['report' => 'grades', 'term' => 'prelim']),
            route('reports.index', ['report' => 'at_risk']),
            route('api.students.summary', $student),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        foreach (['attendance' => 'Attendance Summary', 'grades' => 'Grade Report', 'at_risk' => 'At-Risk Students'] as $report => $title) {
            $csv = $this->get(route('reports.index', ['report' => $report, 'export' => 'csv']));
            $csv->assertOk();
            $this->assertStringContainsString($title, $csv->streamedContent());
        }
    }
}
