<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class StudentsImport implements OnEachRow, WithHeadingRow
{
    public int $total = 0;
    public int $successful = 0;
    public int $failed = 0;
    public array $errors = [];

    protected ?int $defaultClassId;
    protected int $createdByUserId;

    public function __construct(?int $defaultClassId, int $createdByUserId)
    {
        $this->defaultClassId = $defaultClassId;
        $this->createdByUserId = $createdByUserId;
    }

    public function onRow(Row $row)
    {
        $this->total++;
        $index = $row->getIndex();            // 2..N (1 is header because WithHeadingRow)
        $data  = $row->toArray();             // associative by heading row

        try {
            $mapped = $this->mapRow($data);

            // Check duplicates by username/email/student_code
            if (
                User::where('username', $mapped['username'])->exists() ||
                User::where('email', $mapped['email'])->exists() ||
                Student::where('student_code', $mapped['student_code'])->exists()
            ) {
                $this->failed++;
                $this->errors["row_{$index}"] = 'Duplicate username/email/student_code';
                return;
            }

            DB::transaction(function () use ($mapped) {
                // Create user
                $user = User::create([
                    'username'      => $mapped['username'],
                    'email'         => $mapped['email'],
                    'password_hash' => bcrypt('student123'), // default
                    'role'          => 'student',
                    'first_name'    => $mapped['first_name'],
                    'last_name'     => $mapped['last_name'],
                    'is_active'     => true,
                ]);

                // Create student profile
                Student::create([
                    'user_id'          => $user->id,
                    'student_code'     => $mapped['student_code'],
                    'date_of_birth'    => $mapped['date_of_birth'],
                    'gender'           => $mapped['gender'],
                    'address'          => $mapped['address'],
                    'parent_name'      => $mapped['parent_name'],
                    'parent_phone'     => $mapped['parent_phone'],
                    'enrollment_date'  => $mapped['enrollment_date'] ?? now(),
                    'current_class_id' => $mapped['current_class_id'],
                ]);
            });

            $this->successful++;
        } catch (\Throwable $e) {
            $this->failed++;
            $this->errors["row_{$index}"] = $e->getMessage();
        }
    }

    /**
     * Normalize one row from CSV/XLSX.
     * Handles Excel serial dates and text dates.
     */
    protected function mapRow(array $row): array
    {
        // Trim keys to lower snake-case expectations
        $val = fn($key, $default = null) => isset($row[$key]) && $row[$key] !== '' ? trim((string)$row[$key]) : $default;

        // Parse dates: support "YYYY-MM-DD", "DD/MM/YYYY", and Excel serials
        $parseDate = function ($raw) {
            if ($raw === null || $raw === '') return null;

            // numeric excel serial?
            if (is_numeric($raw)) {
                try {
                    // Excel epoch: 1899-12-30
                    return Carbon::createFromTimestampUTC(((int)$raw - 25569) * 86400)->startOfDay();
                } catch (\Throwable) {
                    // fall through to string parse
                }
            }

            // common string formats
            try { return Carbon::parse($raw); } catch (\Throwable) {}
            try { return Carbon::createFromFormat('d/m/Y', $raw); } catch (\Throwable) {}
            try { return Carbon::createFromFormat('m/d/Y', $raw); } catch (\Throwable) {}

            // Last resort
            return null;
        };

        $currentClassId = $val('current_class_id');
        if (!$currentClassId && $this->defaultClassId) {
            $currentClassId = $this->defaultClassId;
        }

        return [
            'first_name'       => $val('first_name', ''),
            'last_name'        => $val('last_name', ''),
            'username'         => $val('username', ''),
            'email'            => $val('email', ''),
            'student_code'     => $val('student_code', ''),
            'date_of_birth'    => $parseDate($val('date_of_birth')),
            'gender'           => $val('gender'),
            'address'          => $val('address'),
            'parent_name'      => $val('parent_name'),
            'parent_phone'     => $val('parent_phone'),
            'enrollment_date'  => $parseDate($val('enrollment_date')),
            'current_class_id' => $currentClassId ? (int)$currentClassId : null,
        ];
    }
}
