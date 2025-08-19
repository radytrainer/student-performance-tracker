<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CambodiaSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cambodiaSchools = [
            [
                'name' => 'Passerelles Numeriques Cambodia (PNC)',
                'code' => 'PNC001',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-23-xxx-xxx',
                'email' => 'info@pnc.org.kh',
                'website' => 'https://pnc.org.kh',
                'description' => 'Passerelles numériques Cambodia provides free IT and soft skills training to underprivileged young people.',
                'status' => 'active'
            ],
            [
                'name' => 'Pour un Sourire d\'Enfant (PSE)',
                'code' => 'PSE001',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-23-xxx-xxx',
                'email' => 'contact@pse-cambodia.org',
                'website' => 'https://pse-cambodia.org',
                'description' => 'Pour un Sourire d\'Enfant helps disadvantaged children in Cambodia through education and social programs.',
                'status' => 'active'
            ],
            [
                'name' => 'Royal University of Phnom Penh (RUPP)',
                'code' => 'RUPP001',
                'address' => 'Russian Federation Blvd, Phnom Penh, Cambodia',
                'phone' => '+855-23-880-745',
                'email' => 'info@rupp.edu.kh',
                'website' => 'https://rupp.edu.kh',
                'description' => 'The Royal University of Phnom Penh is the oldest and largest university in Cambodia.',
                'status' => 'active'
            ],
            [
                'name' => 'Institute of Technology of Cambodia (ITC)',
                'code' => 'ITC001',
                'address' => 'Russian Federation Blvd, Phnom Penh, Cambodia',
                'phone' => '+855-23-880-526',
                'email' => 'info@itc.edu.kh',
                'website' => 'https://itc.edu.kh',
                'description' => 'Institute of Technology of Cambodia is the leading engineering and technology university in Cambodia.',
                'status' => 'active'
            ],
            [
                'name' => 'American University of Phnom Penh (AUPP)',
                'code' => 'AUPP001',
                'address' => 'St. 271, Phnom Penh, Cambodia',
                'phone' => '+855-23-990-480',
                'email' => 'admissions@aupp.edu.kh',
                'website' => 'https://aupp.edu.kh',
                'description' => 'American University of Phnom Penh offers American-style higher education in Cambodia.',
                'status' => 'active'
            ],
            [
                'name' => 'Western University (Cambodia)',
                'code' => 'WUC001',
                'address' => 'Battambang, Cambodia',
                'phone' => '+855-53-xxx-xxx',
                'email' => 'info@western.edu.kh',
                'website' => 'https://western.edu.kh',
                'description' => 'Western University provides higher education opportunities in western Cambodia.',
                'status' => 'active'
            ],
            [
                'name' => 'Asia Euro University',
                'code' => 'AEU001',
                'address' => 'Phnom Penh, Cambodia',
                'phone' => '+855-23-xxx-xxx',
                'email' => 'info@aeu.edu.kh',
                'website' => 'https://aeu.edu.kh',
                'description' => 'Asia Euro University offers diverse academic programs with international standards.',
                'status' => 'active'
            ],
            [
                'name' => 'Norton University',
                'code' => 'NU001',
                'address' => 'St. 93, Phnom Penh, Cambodia',
                'phone' => '+855-23-990-520',
                'email' => 'info@norton-u.com',
                'website' => 'https://norton-u.com',
                'description' => 'Norton University is a private university offering various undergraduate and graduate programs.',
                'status' => 'active'
            ]
        ];

        foreach ($cambodiaSchools as $school) {
            // Check if school already exists to avoid duplicates
            $existingSchool = School::where('code', $school['code'])->first();
            if (!$existingSchool) {
                School::create($school);
            }
        }
    }
}
