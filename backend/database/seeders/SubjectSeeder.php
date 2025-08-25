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
                'subject_code' => 'W01',
                'subject_name' => 'Web Design',
                'description' => 'Introduction to web design principles and practices',
                'credit_hours' => 56,
                'department' => 'Web Development',
                'is_core' => true,
            ],
            [
                'subject_code' => 'ENG01',
                'subject_name' => 'General English',
                'description' => 'Reading, writing, grammar, and literature analysis',
                'credit_hours' => 60,
                'department' => 'English',
                'is_core' => true,
            ],
            [
                'subject_code' => 'SCI101',
                'subject_name' => 'Data Analytics',
                'description' => 'Introduction to data analysis and visualization techniques',
                'credit_hours' => 30,
                'department' => 'Data Science',
                'is_core' => true,
            ],
            [
                'subject_code' => 'A01',
                'subject_name' => 'Algorithm',
                'description' => 'Improve problem-solving skills through algorithmic thinking',
                'credit_hours' => 20,
                'department' => 'Social Studies',
                'is_core' => true,
            ],
            [
                'subject_code' => 'P01',
                'subject_name' => 'Professional Life',
                'description' => 'Developing skills for the workplace and professional environments',
                'credit_hours' => 108,
                'department' => 'English',
                'is_core' => false,
            ],
            [
                'subject_code' => 'N01',
                'subject_name' => 'Networking',
                'description' => 'Study of computer networks, protocols, and security',
                'credit_hours' => 18,
                'department' => 'IT',
                'is_core' => false,
            ],
            [
                'subject_code' => 'EDU01',
                'subject_name' => 'ESD',
                'description' => 'Study of sustainable development and environmental issues',
                'credit_hours' => 36,
                'department' => 'Social',
                'is_core' => false,
            ],
            [
                'subject_code' => 'L01',
                'subject_name' => 'Logic',
                'description' => 'Study of reasoning and argumentation',
                'credit_hours' => 30,
                'department' => 'IT',
                'is_core' => false,
            ],
            [
                'subject_code' => 'PE01',
                'subject_name' => 'Physical Education',
                'description' => 'Physical fitness, sports, and health education',
                'credit_hours' => 23,
                'department' => 'Social',
                'is_core' => true,
            ],
            [
                'subject_code' => 'ART01',
                'subject_name' => 'Visual Arts',
                'description' => 'Drawing, painting, sculpture, and art appreciation',
                'credit_hours' => 30,
                'department' => 'Arts',
                'is_core' => false,
            ],
            [
                'subject_code' => 'MUS01',
                'subject_name' => 'Music',
                'description' => 'Music theory, instruments, and performance',
                'credit_hours' => 16,
                'department' => 'Arts',
                'is_core' => false,
            ],
            [
                'subject_code' => 'CS101',
                'subject_name' => 'Computer Science',
                'description' => 'Introduction to programming and computer literacy',
                'credit_hours' => 70,
                'department' => 'Technology',
                'is_core' => false,
            ],
            [
                'subject_code' => 'D01',
                'subject_name' => 'Design',
                'description' => 'Introduction to design principles and practices',
                'credit_hours' => 18,
                'department' => 'IT',
                'is_core' => false,
            ],
            [
                'subject_code' => 'ECON101',
                'subject_name' => 'Economics',
                'description' => 'Basic economic principles and concepts',
                'credit_hours' => 8,
                'department' => 'Social',
                'is_core' => false,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
