<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'name' => $this->name,
            'program' => $this->program,
            'year_level' => $this->year_level,
            'gpa' => round((float) $this->gpa, 2),
            'attendance_rate' => (int) $this->attendance_rate,
            'academic_standing' => $this->academic_standing,
            'standing_color' => $this->standing_color,
            'alert_reasons' => $this->alert_reasons,
            'url' => route('students.show', $this->id),
        ];
    }
}
