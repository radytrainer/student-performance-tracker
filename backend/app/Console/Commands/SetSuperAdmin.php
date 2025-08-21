<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-super-admin {email=admin@school.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a user as super admin by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return;
        }

        if ($user->role !== 'admin') {
            $this->error("User must be an admin to become super admin.");
            return;
        }

        $user->update([
            'is_super_admin' => true,
            'school_id' => null // Super admin doesn't belong to a specific school
        ]);

        $this->info("User {$email} has been set as super admin successfully!");
    }
}
