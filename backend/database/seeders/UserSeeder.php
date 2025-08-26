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
        // Admin User (idempotent)
        $admin = User::updateOrCreate(
            ['email' => 'admin@school.com'],
            [
                'username' => 'admin',
                'password_hash' => Hash::make('admin123'),
                'role' => 'admin',
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'is_active' => true,
                'school_id' => 1,
            ]
        );

        // Teacher Users
        $teachers = [
            [
                'username' => 'seavmey.yem',
                'email' => 'seavmey.yem@school.com',
                'first_name' => 'Seavmey',
                'last_name' => 'Yem',
                'teacher_code' => 'TCH001',
                'department' => 'English',
                'qualification' => 'M. English',
                'specialization' => 'Algebra, Calculus',
                'hire_date' => '2025-07-15',
                'school_id' => 1,
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
                'school_id' => 1,
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
                'school_id' => 1,
            ],
            // Test user requested
            [
                'username' => 'sinh.teacher',
                'email' => 'sinh.teacher@school.com',
                'first_name' => 'Sinh',
                'last_name' => 'Teacher',
                'teacher_code' => 'TCH010',
                'department' => 'Computer Science',
                'qualification' => 'M.Ed. CS',
                'specialization' => 'Data Import',
                'hire_date' => '2025-08-01',
                'school_id' => 1,
            ],
            // Standard demo teachers (align with README)
            [
                'username' => 'teacher1',
                'email' => 'teacher1@school.com',
                'first_name' => 'Teacher',
                'last_name' => 'One',
                'teacher_code' => 'TCH101',
                'department' => 'Science',
                'qualification' => 'M.Sc.',
                'specialization' => 'Physics',
                'hire_date' => '2025-01-10',
                'school_id' => 1,
            ],
            [
                'username' => 'teacher2',
                'email' => 'teacher2@school.com',
                'first_name' => 'Teacher',
                'last_name' => 'Two',
                'teacher_code' => 'TCH102',
                'department' => 'Mathematics',
                'qualification' => 'M.Sc.',
                'specialization' => 'Algebra',
                'hire_date' => '2025-01-10',
                'school_id' => 1,
            ],
            [
                'username' => 'teacher3',
                'email' => 'teacher3@school.com',
                'first_name' => 'Teacher',
                'last_name' => 'Three',
                'teacher_code' => 'TCH103',
                'department' => 'English',
                'qualification' => 'M.A.',
                'specialization' => 'Literature',
                'hire_date' => '2025-01-10',
                'school_id' => 1,
            ],
        ];

        foreach ($teachers as $teacherData) {
            $user = User::updateOrCreate(
                ['username' => $teacherData['username']],
                [
                    'email' => $teacherData['email'],
                    'password_hash' => Hash::make('teacher123'),
                    'role' => 'teacher',
                    'first_name' => $teacherData['first_name'],
                    'last_name' => $teacherData['last_name'],
                    'is_active' => true,
                    'school_id' => $teacherData['school_id'] ?? null,
                ]
            );

            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'teacher_code' => $teacherData['teacher_code'],
                    'department' => $teacherData['department'],
                    'qualification' => $teacherData['qualification'],
                    'specialization' => $teacherData['specialization'],
                    'hire_date' => $teacherData['hire_date'],
                ]
            );
        }

        // Student Users
        $students = [
            [
                'username' => 'sophy.em',
                'email' => 'sophy.em@student.school.com',
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
            // Standard demo students
            [
                'username' => 'student1',
                'email' => 'student1@school.com',
                'first_name' => 'Student',
                'last_name' => 'One',
                'student_code' => 'STU1001',
                'date_of_birth' => '2008-01-01',
                'gender' => 'Female',
                'address' => '100 Demo Street',
                'parent_name' => 'Parent One',
                'parent_phone' => '+855-555-0201',
                'enrollment_date' => '2025-06-01',
            ],
            [
                'username' => 'student2',
                'email' => 'student2@school.com',
                'first_name' => 'Student',
                'last_name' => 'Two',
                'student_code' => 'STU1002',
                'date_of_birth' => '2008-02-02',
                'gender' => 'Male',
                'address' => '101 Demo Street',
                'parent_name' => 'Parent Two',
                'parent_phone' => '+855-555-0202',
                'enrollment_date' => '2025-06-01',
            ],
            [
                'username' => 'student3',
                'email' => 'student3@school.com',
                'first_name' => 'Student',
                'last_name' => 'Three',
                'student_code' => 'STU1003',
                'date_of_birth' => '2008-03-03',
                'gender' => 'Female',
                'address' => '102 Demo Street',
                'parent_name' => 'Parent Three',
                'parent_phone' => '+855-555-0203',
                'enrollment_date' => '2025-06-01',
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::updateOrCreate(
                ['email' => $studentData['email']],
                [
                    'username' => $studentData['username'],
                    'password_hash' => Hash::make('student123'),
                    'role' => 'student',
                    'first_name' => $studentData['first_name'],
                    'last_name' => $studentData['last_name'],
                    'is_active' => true,
                ]
            );

            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_code' => $studentData['student_code'],
                    'date_of_birth' => $studentData['date_of_birth'],
                    'gender' => $studentData['gender'],
                    'address' => $studentData['address'],
                    'parent_name' => $studentData['parent_name'],
                    'parent_phone' => $studentData['parent_phone'],
                    'enrollment_date' => $studentData['enrollment_date'],
                ]
            );
        }
    }
}
