<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            [
                'subject_code' => 'MATH101',
                'subject_name' => 'Mathematics',
                'description' => 'Basic mathematics including algebra, geometry, and calculus',
                'credit_hours' => 3,
                'department' => 'Mathematics',
                'is_core' => true,
            ],
            [
                'subject_code' => 'ENG101',
                'subject_name' => 'English Language Arts',
                'description' => 'Reading, writing, grammar, and literature analysis',
                'credit_hours' => 3,
                'department' => 'English',
                'is_core' => true,
            ],
            [
                'subject_code' => 'SCI101',
                'subject_name' => 'General Science',
                'description' => 'Introduction to basic scientific concepts and methodology',
                'credit_hours' => 3,
                'department' => 'Science',
                'is_core' => true,
            ],
            [
                'subject_code' => 'HIST101',
                'subject_name' => 'World History',
                'description' => 'Study of major world events and civilizations',
                'credit_hours' => 2,
                'department' => 'Social Studies',
                'is_core' => true,
            ],
            [
                'subject_code' => 'GEO101',
                'subject_name' => 'Geography',
                'description' => 'Physical and human geography, map skills',
                'credit_hours' => 2,
                'department' => 'Social Studies',
                'is_core' => false,
            ],
            [
                'subject_code' => 'PHY201',
                'subject_name' => 'Physics',
                'description' => 'Study of matter, energy, motion, and forces',
                'credit_hours' => 3,
                'department' => 'Science',
                'is_core' => false,
            ],
            [
                'subject_code' => 'CHEM201',
                'subject_name' => 'Chemistry',
                'description' => 'Study of chemical elements, compounds, and reactions',
                'credit_hours' => 3,
                'department' => 'Science',
                'is_core' => false,
            ],
            [
                'subject_code' => 'BIO201',
                'subject_name' => 'Biology',
                'description' => 'Study of living organisms and life processes',
                'credit_hours' => 3,
                'department' => 'Science',
                'is_core' => false,
            ],
            [
                'subject_code' => 'PE101',
                'subject_name' => 'Physical Education',
                'description' => 'Physical fitness, sports, and health education',
                'credit_hours' => 1,
                'department' => 'Physical Education',
                'is_core' => true,
            ],
            [
                'subject_code' => 'ART101',
                'subject_name' => 'Visual Arts',
                'description' => 'Drawing, painting, sculpture, and art appreciation',
                'credit_hours' => 2,
                'department' => 'Arts',
                'is_core' => false,
            ],
            [
                'subject_code' => 'MUS101',
                'subject_name' => 'Music',
                'description' => 'Music theory, instruments, and performance',
                'credit_hours' => 2,
                'department' => 'Arts',
                'is_core' => false,
            ],
            [
                'subject_code' => 'CS101',
                'subject_name' => 'Computer Science',
                'description' => 'Introduction to programming and computer literacy',
                'credit_hours' => 2,
                'department' => 'Technology',
                'is_core' => false,
            ],
            [
                'subject_code' => 'SPAN101',
                'subject_name' => 'Spanish',
                'description' => 'Introduction to Spanish language and culture',
                'credit_hours' => 2,
                'department' => 'World Languages',
                'is_core' => false,
            ],
            [
                'subject_code' => 'ECON101',
                'subject_name' => 'Economics',
                'description' => 'Basic economic principles and concepts',
                'credit_hours' => 2,
                'department' => 'Social Studies',
                'is_core' => false,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
