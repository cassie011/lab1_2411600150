<?php

namespace App\Http\Resources;

use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row in the dashboard "Recent Activity" table.
 * Wraps either a Grade or an AttendanceRecord.
 */
class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isGrade = $this->resource instanceof Grade;

        return [
            'id' => $this->id,
            'kind' => $isGrade ? 'grade' : 'attendance',
            'label' => $isGrade ? 'Grade' : 'Attendance',
            'detail' => $isGrade
                ? "{$this->subject} ({$this->term_label}): " . number_format((float) $this->grade, 2)
                : $this->status_label . ' on ' . $this->date->format('M d'),
            'color' => $isGrade ? $this->grade_color : $this->status_color,
            'student' => [
                'id' => $this->student?->id,
                'name' => $this->student?->name,
            ],
            'user' => $this->user?->name,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'url' => $isGrade
                ? route('grades.show', $this->id)
                : route('students.show', $this->student_id),
        ];
    }
}
