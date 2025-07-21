<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StudentGradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [];

        $assessmentTypes = ['quiz', 'assignment', 'exam'];
        $gradeLetters = ['A', 'B', 'C', 'D', 'F'];

        for ($i = 1; $i <= 10; $i++) {
            $maxScore = rand(50, 100);
            $score = rand(0, $maxScore);
            $grades[] = [
                'student_id' => rand(1, 10),
                'class_subject_id' => rand(1, 5),
                'term_id' => rand(1, 3),
                'assessment_type' => $assessmentTypes[array_rand($assessmentTypes)],
                'max_score' => $maxScore,
                'score_obtained' => $score,
                'weightage' => rand(10, 40), // as percent
                'grade_letter' => $gradeLetters[array_rand($gradeLetters)],
                'remarks' => fake()->sentence(),
                'recorded_by' => 1,
                'recorded_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('student_grades')->insert($grades);
    }
}
