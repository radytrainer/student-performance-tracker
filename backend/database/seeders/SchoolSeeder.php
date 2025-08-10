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
                'name' => 'Lincoln High School',
                'code' => 'LHS001',
                'address' => '123 Education Street, New York, NY 10001',
                'phone' => '+1-555-0101',
                'email' => 'admin@lincolnhigh.edu',
                'website' => 'https://lincolnhigh.edu',
                'description' => 'A premier educational institution committed to academic excellence and student development.',
                'status' => 'active'
            ],
            [
                'name' => 'Washington Elementary',
                'code' => 'WES002',
                'address' => '456 Learning Avenue, Boston, MA 02101',
                'phone' => '+1-555-0102',
                'email' => 'info@washingtonelem.edu',
                'website' => 'https://washingtonelem.edu',
                'description' => 'Nurturing young minds with innovative teaching methods and comprehensive curriculum.',
                'status' => 'active'
            ],
            [
                'name' => 'Roosevelt Academy',
                'code' => 'ROA003',
                'address' => '789 Knowledge Blvd, Chicago, IL 60601',
                'phone' => '+1-555-0103',
                'email' => 'contact@rooseveltacademy.edu',
                'website' => 'https://rooseveltacademy.edu',
                'description' => 'Excellence in education through technology-enhanced learning and personalized instruction.',
                'status' => 'active'
            ]
        ];

        foreach ($schools as $school) {
            School::create($school);
        }
    }
}
