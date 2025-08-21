<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use App\Models\Classes;
use Illuminate\Console\Command;

class UpdateSchoolAssociations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-school-associations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing users and classes with school associations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $school = School::first();
        
        if (!$school) {
            $this->error('No schools found. Please run SchoolSeeder first.');
            return;
        }

        // Update users without school_id
        $usersUpdated = User::whereNull('school_id')->update(['school_id' => $school->id]);
        $this->info("Updated {$usersUpdated} users with school association.");

        // Update classes without school_id
        $classesUpdated = Classes::whereNull('school_id')->update(['school_id' => $school->id]);
        $this->info("Updated {$classesUpdated} classes with school association.");

        $this->info('School associations updated successfully!');
    }
}
