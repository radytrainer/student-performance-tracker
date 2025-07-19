<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in proper order to maintain referential integrity
        $this->call([
            UserSeeder::class,           // Create users first (admin, teachers, students)
            SubjectSeeder::class,        // Create subjects
            TermSeeder::class,           // Create academic terms
            ClassSeeder::class,          // Create classes and assign students
            ClassSubjectSeeder::class,   // Assign subjects to classes with teachers
            StudentClassSeeder::class,   // Create student-class enrollment records
            SystemSettingSeeder::class,  // Create system settings (requires admin user)
        ]);

        $this->command->info('Database seeded successfully with comprehensive initial data!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@school.com / admin123');
        $this->command->info('Teachers: [firstname.lastname]@school.com / teacher123');
        $this->command->info('Students: [firstname.lastname]@student.school.com / student123');
    }
}
