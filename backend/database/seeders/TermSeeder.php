<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Term;

class TermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terms = [
            [
                'term_name' => 'Fall 2024',
                'academic_year' => '2024-2025',
                'start_date' => '2024-08-20',
                'end_date' => '2024-12-15',
                'is_current' => false,
            ],
            [
                'term_name' => 'Spring 2025',
                'academic_year' => '2024-2025',
                'start_date' => '2025-01-15',
                'end_date' => '2025-05-30',
                'is_current' => true,
            ],
            [
                'term_name' => 'Summer 2025',
                'academic_year' => '2024-2025',
                'start_date' => '2025-06-15',
                'end_date' => '2025-07-31',
                'is_current' => false,
            ],
            [
                'term_name' => 'Fall 2025',
                'academic_year' => '2025-2026',
                'start_date' => '2025-08-20',
                'end_date' => '2025-12-15',
                'is_current' => false,
            ],
        ];

        foreach ($terms as $term) {
            Term::create($term);
        }
    }
}
