<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Teacher;
use App\Models\Classes;

class TeacherClassAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'teacher_id' => [
                'required',
                'exists:teachers,user_id',
                function ($attribute, $value, $fail) {
                    // Check if teacher is active
                    $teacher = Teacher::with('user')->where('user_id', $value)->first();
                    if (!$teacher || !$teacher->user->is_active) {
                        $fail('The selected teacher is not active.');
                    }

                    // Check class load limit
                    $maxClasses = config('app.max_classes_per_teacher', 5);
                    $currentClasses = Classes::where('class_teacher_id', $value)->count();
                    
                    if ($currentClasses >= $maxClasses) {
                        $fail("Teacher already assigned to maximum allowed classes ($maxClasses).");
                    }
                }
            ]
        ];
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            'teacher_id.required' => 'Please select a teacher.',
            'teacher_id.exists' => 'The selected teacher does not exist.',
        ];
    }
}
