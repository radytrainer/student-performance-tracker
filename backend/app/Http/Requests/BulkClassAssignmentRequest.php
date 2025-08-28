<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Classes;

class BulkClassAssignmentRequest extends FormRequest
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
            'class_ids' => [
                'required',
                'array',
                'min:1',
                'max:' . config('app.max_classes_per_teacher', 5)
            ],
            'class_ids.*' => [
                'exists:classes,id',
                function ($attribute, $value, $fail) {
                    // Check if class exists and get more details if needed
                    $class = Classes::find($value);
                    if (!$class) {
                        $fail('One or more selected classes do not exist.');
                    }
                }
            ],
            'replace_existing' => 'boolean'
        ];
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            'class_ids.required' => 'Please select at least one class.',
            'class_ids.array' => 'Class selection must be an array.',
            'class_ids.min' => 'Please select at least one class.',
            'class_ids.max' => 'Cannot assign more than ' . config('app.max_classes_per_teacher', 5) . ' classes at once.',
            'class_ids.*.exists' => 'One or more selected classes do not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure class_ids are integers
        if ($this->has('class_ids') && is_array($this->class_ids)) {
            $this->merge([
                'class_ids' => array_map('intval', $this->class_ids)
            ]);
        }

        // Set default for replace_existing
        if (!$this->has('replace_existing')) {
            $this->merge(['replace_existing' => false]);
        }
    }
}
