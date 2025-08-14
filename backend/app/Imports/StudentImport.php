<?php
namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class StudentImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    public function model(array $row)
    {
        try {
            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'username'      => $row['username'],
                    'first_name'    => $row['first_name'],
                    'last_name'     => $row['last_name'],
                    'password_hash' => bcrypt('student123'),
                    'role'          => 'student',
                    'is_active'     => true
                ]
            );

            if (!Student::where('user_id', $user->id)->exists()) {
                return new Student([
                    'user_id'          => $user->id,
                    'student_code'     => $row['student_code'],
                    'date_of_birth'    => $row['date_of_birth'],
                    'gender'           => $row['gender'],
                    'address'          => $row['address'],
                    'parent_name'      => $row['parent_name'],
                    'parent_phone'     => $row['parent_phone'],
                    'enrollment_date'  => $row['enrollment_date'],
                    'current_class_id' => $row['current_class_id'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Student import error: ' . $e->getMessage(), $row);
        }

        return null;
    }

    public function rules(): array
    {
        return [
            '*.email' => 'required|email',
            '*.username' => 'required|string',
            '*.first_name' => 'required|string',
            '*.last_name' => 'required|string',
            '*.student_code' => 'required|string',
            '*.enrollment_date' => 'required|date',
        ];
    }
}
