<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@school.com',
            'password_hash' => Hash::make('admin123'),
            'role' => 'admin',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'is_active' => true,
        ]);

        // Teacher Users
        $teachers = [
            [
                'username' => 'seavmey.yem',
                'email' => 'seaymey.yem@gmail.com',
                'first_name' => 'Seavmey',
                'last_name' => 'Yem',
                'teacher_code' => 'TCH001',
                'department' => 'English',
                'qualification' => 'M. English',
                'specialization' => 'Algebra, Calculus',
                'hire_date' => '2025-07-15',
            ],
            [
                'username' => 'sinh.ern',
                'email' => 'sinh.ern@school.com',
                'first_name' => 'Sinh',
                'last_name' => 'Ern',
                'teacher_code' => 'TCH002',
                'department' => 'Web Development',
                'qualification' => 'M.Sc. Backend, B.Ed.',
                'specialization' => 'PHP, Laravel',
                'hire_date' => '2025-02-22',
            ],
            [
                'username' => 'panhapich.rin',
                'email' => 'panhapich.rin@school.com',
                'first_name' => 'Panhapich',
                'last_name' => 'Rin',
                'teacher_code' => 'TCH003',
                'department' => 'English',
                'qualification' => 'M.A. English Literature',
                'specialization' => 'Literature, Creative Writing',
                'hire_date' => '2025-06-10',
            ],
        ];

        foreach ($teachers as $teacherData) {
            $teacher = User::create([
                'username' => $teacherData['username'],
                'email' => $teacherData['email'],
                'password_hash' => Hash::make('teacher123'),
                'role' => 'teacher',
                'first_name' => $teacherData['first_name'],
                'last_name' => $teacherData['last_name'],
                'is_active' => true,
            ]);

            Teacher::create([
                'user_id' => $teacher->id,
                'teacher_code' => $teacherData['teacher_code'],
                'department' => $teacherData['department'],
                'qualification' => $teacherData['qualification'],
                'specialization' => $teacherData['specialization'],
                'hire_date' => $teacherData['hire_date'],
            ]);
        }

        // Student Users
        $students = [
            [
                'username' => 'sophy.em',
                'email' => 'sophy.em@student.com',
                'first_name' => 'Sophy',
                'last_name' => 'Em',
                'student_code' => 'STU001',
                'date_of_birth' => '2008-03-15',
                'gender' => 'Male',
                'address' => '123 Oak Street, Springfield, IL',
                'parent_name' => 'Robert Smith',
                'parent_phone' => '+855-555-0101',
                'enrollment_date' => '2025-07-20',
            ],
            [
                'username' => 'liya.an',
                'email' => 'liya.an@student.school.com',
                'first_name' => 'Liya',
                'last_name' => 'An',
                'student_code' => 'STU002',
                'date_of_birth' => '2008-07-22',
                'gender' => 'Female',
                'address' => '456 Maple Avenue, Springfield, IL',
                'parent_name' => 'Lisa Wilson',
                'parent_phone' => '+855-555-0102',
                'enrollment_date' => '2025-06-20',
            ],
            [
                'username' => 'alex.garcia',
                'email' => 'alex.garcia@student.school.com',
                'first_name' => 'Alexander',
                'last_name' => 'Garcia',
                'student_code' => 'STU003',
                'date_of_birth' => '2008-11-08',
                'gender' => 'Male',
                'address' => '789 Pine Road, Springfield, IL',
                'parent_name' => 'Maria Garcia',
                'parent_phone' => '+855-555-0103',
                'enrollment_date' => '2025-06-20',
            ],
            [
                'username' => 'sophia.lee',
                'email' => 'sophia.lee@student.school.com',
                'first_name' => 'Sophia',
                'last_name' => 'Lee',
                'student_code' => 'STU004',
                'date_of_birth' => '2007-12-03',
                'gender' => 'Female',
                'address' => '321 Elm Street, Springfield, IL',
                'parent_name' => 'David Lee',
                'parent_phone' => '+855-555-0104',
                'enrollment_date' => '2025-06-20',
            ],
            [
                'username' => 'james.taylor',
                'email' => 'james.taylor@student.school.com',
                'first_name' => 'James',
                'last_name' => 'Taylor',
                'student_code' => 'STU005',
                'date_of_birth' => '2008-05-18',
                'gender' => 'Male',
                'address' => '654 Cedar Lane, Springfield, IL',
                'parent_name' => 'Jennifer Taylor',
                'parent_phone' => '+855-555-0105',
                'enrollment_date' => '2025-06-20',
            ],
            [
                'username' => 'olivia.martin',
                'email' => 'olivia.martin@student.school.com',
                'first_name' => 'Olivia',
                'last_name' => 'Martin',
                'student_code' => 'STU006',
                'date_of_birth' => '2008-09-25',
                'gender' => 'Female',
                'address' => '987 Birch Avenue, Springfield, IL',
                'parent_name' => 'Michael Martin',
                'parent_phone' => '+855-555-0106',
                'enrollment_date' => '2025-05-20',
            ],
        ];

        foreach ($students as $studentData) {
            $student = User::create([
                'username' => $studentData['username'],
                'email' => $studentData['email'],
                'password_hash' => Hash::make('student123'),
                'role' => 'student',
                'first_name' => $studentData['first_name'],
                'last_name' => $studentData['last_name'],
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $student->id,
                'student_code' => $studentData['student_code'],
                'date_of_birth' => $studentData['date_of_birth'],
                'gender' => $studentData['gender'],
                'address' => $studentData['address'],
                'parent_name' => $studentData['parent_name'],
                'parent_phone' => $studentData['parent_phone'],
                'enrollment_date' => $studentData['enrollment_date'],
            ]);
        }
    }
}
