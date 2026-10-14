<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /notifications/{id}/read
    // Marks one notification as read, then opens the related student.
    public function read(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        $studentId = $notification->data['student_id'] ?? null;

        // Students go to their own portal, teachers/admins to the student page
        if ($request->user()->isStudent() || ! $studentId) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('students.show', $studentId);
    }

    // POST /notifications/read-all
    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
