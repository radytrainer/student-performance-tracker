<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\AuthorizationService;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can create users
        return AuthorizationService::hasRole($this->user(), 'admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,teacher,student',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ];

        // Role-specific validation
        if ($this->input('role') === 'teacher') {
            $rules = array_merge($rules, [
                'teacher_code' => 'required|string|unique:teachers,teacher_code',
                'department' => 'nullable|string|max:100',
                'qualification' => 'nullable|string',
                'specialization' => 'nullable|string|max:100',
                'hire_date' => 'required|date',
            ]);
        }

        if ($this->input('role') === 'student') {
            $rules = array_merge($rules, [
                'student_code' => 'required|string|unique:students,student_code',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:male,female,other',
                'address' => 'nullable|string',
                'parent_name' => 'nullable|string|max:100',
                'parent_phone' => 'nullable|string|max:20',
                'enrollment_date' => 'required|date',
                'current_class_id' => 'nullable|exists:classes,id',
            ]);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'role.in' => 'Role must be admin, teacher, or student.',
            'teacher_code.required' => 'Teacher code is required for teacher accounts.',
            'student_code.required' => 'Student code is required for student accounts.',
            'hire_date.required' => 'Hire date is required for teacher accounts.',
            'enrollment_date.required' => 'Enrollment date is required for student accounts.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional role-based validations
            $role = $this->input('role');
            $user = $this->user();

            // Only super admin can create admin users
            if ($role === 'admin' && !AuthorizationService::canChangeUserRole($user, new \App\Models\User(), 'admin')) {
                $validator->errors()->add('role', 'You do not have permission to create admin users.');
            }
        });
    }
}
