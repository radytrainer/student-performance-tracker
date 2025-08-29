<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTeacherSubjectsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => [
                'integer',
                'distinct',
                'exists:subjects,id'
            ],
            'primary_subject_id' => [
                'nullable',
                'integer',
                Rule::in($this->input('subject_ids', []))
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subject_ids.required' => 'At least one subject must be selected',
            'subject_ids.array' => 'Subject IDs must be provided as an array',
            'subject_ids.min' => 'At least one subject must be selected',
            'subject_ids.*.integer' => 'Each subject ID must be a valid number',
            'subject_ids.*.distinct' => 'Duplicate subject IDs are not allowed',
            'subject_ids.*.exists' => 'One or more selected subjects do not exist',
            'primary_subject_id.in' => 'Primary subject must be one of the selected subjects'
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors();
        
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422)
        );
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation for school isolation
            if ($this->user() && $this->user()->isSchoolAdmin()) {
                $teacher = $this->route('teacher');
                
                if ($teacher && $teacher->school_id !== $this->user()->school_id) {
                    $validator->errors()->add('teacher', 'You can only assign subjects to teachers from your school');
                }
            }

            // Validate that teacher exists and is actually a teacher
            $teacher = $this->route('teacher');
            if ($teacher && $teacher->role !== 'teacher') {
                $validator->errors()->add('teacher', 'The specified user is not a teacher');
            }
        });
    }
}
