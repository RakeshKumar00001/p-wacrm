<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\Hash;

class SeedSuperAdmin extends Command
{
    protected $signature   = 'wacrm:seed-superadmin';
    protected $description = 'Create the super admin user account';

    public function handle(): void
    {
        $business = Business::first();

        if (!$business) {
            $this->error('No business found. Please run the main seeder first.');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => 'superadmin@wacrm.io'],
            [
                'name'        => 'Super Admin',
                'password'    => Hash::make('Admin@1234'),
                'role'        => 'super_admin',
                'business_id' => $business->id,
            ]
        );

        $this->info("Super Admin created/updated:");
        $this->line("  Email   : superadmin@wacrm.io");
        $this->line("  Password: Admin@1234");
        $this->line("  URL     : /login → /super-admin");
        $this->warn("  ⚠ Change the password immediately after first login!");
    }
}
