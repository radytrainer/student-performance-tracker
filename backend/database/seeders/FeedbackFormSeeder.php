<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FeedbackForm;
use App\Models\FormAssignment;
use App\Models\User;
use App\Models\Classes;

class FeedbackFormSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get a teacher to create forms
        $teacher = User::where('role', 'teacher')->first();
        
        if (!$teacher) {
            echo "No teachers found. Skipping feedback form seeding.\n";
            return;
        }

        // Create sample feedback forms
        $forms = [
            [
                'title' => 'Monthly Teaching Quality Survey',
                'description' => 'Please provide feedback on the teaching quality and classroom experience for this month.',
                'survey_type' => 'monthly',
                'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSdKGgdOgK9VLUXkU7Y8QtS8v6VrXoUqjR8e0L1YvT9HjNz9xA/viewform',
                'google_form_id' => '1FAIpQLSdKGgdOgK9VLUXkU7Y8QtS8v6VrXoUqjR8e0L1YvT9HjNz9xA',
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(23),
                'questions' => [
                    'How would you rate the overall teaching quality?',
                    'How clear and understandable are the lesson explanations?',
                    'How effectively does the teacher engage with students?',
                    'How well does the teacher provide feedback on your work?',
                    'How would you rate your overall learning experience?'
                ]
            ],
            [
                'title' => 'Weekly Class Feedback',
                'description' => 'Quick weekly feedback about this week\'s classes and activities.',
                'survey_type' => 'weekly',
                'google_form_url' => 'https://docs.google.com/forms/d/e/1FAIpQLSfGgdOgK9VLUXkU7Y8QtS8v6VrXoUqjR8e0L1YvT9HjNz9xB/viewform',
                'google_form_id' => '1FAIpQLSfGgdOgK9VLUXkU7Y8QtS8v6VrXoUqjR8e0L1YvT9HjNz9xB',
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(5),
                'questions' => [
                    'How was the pace of lessons this week?',
                    'How well did you understand the content covered?',
                    'How engaging were the activities this week?',
                    'How helpful was the teacher support?',
                    'How would you rate this week overall?'
                ]
            ]
        ];

        foreach ($forms as $formData) {
            $form = FeedbackForm::create([
                'title' => $formData['title'],
                'description' => $formData['description'],
                'survey_type' => $formData['survey_type'],
                'google_form_url' => $formData['google_form_url'],
                'google_form_id' => $formData['google_form_id'],
                'start_date' => $formData['start_date'],
                'end_date' => $formData['end_date'],
                'questions' => $formData['questions'],
                'created_by_teacher_id' => $teacher->id,
                'is_active' => true,
            ]);

            // Assign to all available classes
            $classes = Classes::take(2)->get(); // Assign to first 2 classes
            
            foreach ($classes as $class) {
                FormAssignment::create([
                    'feedback_form_id' => $form->id,
                    'class_id' => $class->id,
                    'assigned_by_teacher_id' => $teacher->id,
                    'due_date' => $formData['end_date'],
                    'instructions' => 'Please complete this feedback survey honestly and thoughtfully. Your responses help us improve the learning experience.',
                    'is_active' => true,
                ]);
            }

            echo "Created feedback form: {$form->title} and assigned to {$classes->count()} classes\n";
        }

        echo "Feedback form seeding completed!\n";
    }
}
