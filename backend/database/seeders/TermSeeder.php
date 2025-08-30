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
                'term_name' => 'Term 1',
                'academic_year' => '2024-2025',
                'start_date' => '2024-02-01',
                'end_date' => '2024-05-01',
                'is_current' => false,
            ],
            [
                'term_name' => 'Term 2',
                'academic_year' => '2024-2025',
                'start_date' => '2024-05-01',
                'end_date' => '2024-08-01',
                'is_current' => true,
            ],
            [
                'term_name' => 'Term 3',
                'academic_year' => '2024-2025',
                'start_date' => '2024-08-01',
                'end_date' => '2024-12-01',
                'is_current' => false,
            ],
            [
                'term_name' => 'Term 4',
                'academic_year' => '2024-2026',
                'start_date' => '2024-12-01',
                'end_date' => '2025-05-01',
                'is_current' => false,
            ],
            [
                'term_name' => 'Term 5',
                'academic_year' => '2025-2026',
                'start_date' => '2025-05-01',
                'end_date' => '2025-12-01',
                'is_current' => false,
            ]
        ];

        foreach ($terms as $term) {
            Term::create($term);
        }
    }
}
