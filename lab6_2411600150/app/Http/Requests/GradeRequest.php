<?php

namespace App\Http\Requests;

use App\Models\Grade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for recording (store) and correcting (update) a grade.
 */
class GradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageRecords() ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'subject' => ['required', 'string', 'max:100'],
            'units' => ['required', 'integer', 'min:1', 'max:6'],
            'term' => ['required', Rule::in(array_keys(Grade::TERMS))],
            'grade' => ['required', 'numeric', 'min:0', 'max:4', 'decimal:0,2'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.exists' => 'The selected student does not exist.',
            'grade.max' => 'Grade must be between 0.00 and 4.00.',
            'grade.min' => 'Grade must be between 0.00 and 4.00.',
        ];
    }

    /**
     * "after" hook: one grade per student + subject + term.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $current = $this->route('grade');

                $exists = Grade::where('student_id', $this->input('student_id'))
                    ->where('subject', trim($this->input('subject')))
                    ->where('term', $this->input('term'))
                    ->when($current, fn ($q) => $q->where('id', '!=', $current->id))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'subject',
                        'This student already has a ' . Grade::TERMS[$this->input('term')]
                            . ' grade for ' . trim($this->input('subject')) . '. Edit that grade instead.'
                    );
                }
            },
        ];
    }
}
