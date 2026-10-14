<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentAtRiskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Student $student)
    {
        //
    }

    /**
     * Delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * The bell (database) alert is saved right away (sync).
     * The email is pushed to the queue and sent in the background
     * by: php artisan queue:work
     */
    public function viaConnections(): array
    {
        return [
            'database' => 'sync',
            'mail' => config('queue.default'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->student;

        $mail = (new MailMessage)
            ->subject("Academic Alert: {$student->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$student->name} ({$student->student_id}, {$student->program}) needs attention:");

        foreach ($student->alert_reasons as $reason) {
            $mail->line("- {$reason}");
        }

        return $mail
            ->action('View Records', $notifiable->isStudent()
                ? route('dashboard')
                : route('students.show', $student))
            ->line('Suggested action: schedule a consultation with the student.');
    }

    /**
     * Data saved in the notifications table (shown in the bell dropdown).
     */
    public function toArray(object $notifiable): array
    {
        $student = $this->student;

        return [
            'student_id' => $student->id,
            'student_number' => $student->student_id,
            'student_name' => $student->name,
            'gpa' => (float) $student->gpa,
            'attendance_rate' => (int) $student->attendance_rate,
            'reasons' => $student->alert_reasons,
            'message' => "{$student->name} needs attention: " . implode('; ', $student->alert_reasons) . '.',
            'suggested_action' => 'Schedule a consultation.',
        ];
    }
}
