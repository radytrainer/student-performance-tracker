<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            [
                'name' => "Passerelles Numeriques Cambodia (PNC)",
                'code' => 'PNC',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-000-000',
                'email' => 'info@pnc.org',
                'website' => 'https://passerellesnumeriques.org/en/our-actions/cambodia/',
                'description' => 'Digital training and professional integration for underprivileged youth.',
                'status' => 'active'
            ],
            [
                'name' => 'Pour un Sourire d\'Enfant (PSE)',
                'code' => 'PSE',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-000-001',
                'email' => 'info@pse.ngo',
                'website' => 'https://www.pse.ngo/en',
                'description' => 'Supporting children and families through education and training.',
                'status' => 'active'
            ],
            [
                'name' => 'Royal University of Phnom Penh (RUPP)',
                'code' => 'RUPP',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-000-002',
                'email' => 'info@rupp.edu.kh',
                'website' => 'https://rupp.edu.kh',
                'description' => 'Cambodia’s oldest and largest public university.',
                'status' => 'active'
            ],
            [
                'name' => 'Institute of Technology of Cambodia (ITC)',
                'code' => 'ITC',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-000-003',
                'email' => 'info@itc.edu.kh',
                'website' => 'https://itc.edu.kh',
                'description' => 'Leading engineering and technology institute in Cambodia.',
                'status' => 'active'
            ],
            [
                'name' => 'American University of Phnom Penh (AUPP)',
                'code' => 'AUPP',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-000-004',
                'email' => 'info@aupp.edu.kh',
                'website' => 'https://www.aupp.edu.kh',
                'description' => 'American-style higher education in Cambodia.',
                'status' => 'active'
            ],
            [
                'name' => 'Western University (Cambodia)',
                'code' => 'WUC',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-000-005',
                'email' => 'info@wuc.edu.kh',
                'website' => 'https://wuc.edu.kh',
                'description' => 'Private university offering diverse programs.',
                'status' => 'active'
            ],
            [
                'name' => 'Asia Euro University',
                'code' => 'AEU',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-000-006',
                'email' => 'info@aeu.edu.kh',
                'website' => 'https://aeu.edu.kh',
                'description' => 'Private university in Cambodia.',
                'status' => 'active'
            ],
            [
                'name' => 'Norton University',
                'code' => 'NORTON',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-000-007',
                'email' => 'info@norton.edu.kh',
                'website' => 'https://norton.edu.kh',
                'description' => 'One of Cambodia’s private universities.',
                'status' => 'active'
            ],
        ];

        foreach ($schools as $school) {
            School::create($school);
        }
    }
}
