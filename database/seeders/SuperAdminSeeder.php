<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Check if SuperAdmin already exists
        $superAdmin = User::where('email', 'superadmin@system.com')->first();
        
        if (!$superAdmin) {
            // Create Super Admin (not tied to any company)
            DB::table('users')->insert([
                'name' => 'Super Admin',
                'email' => 'superadmin@system.com',
                'password' => Hash::make('password123'),
                'company_id' => null,
                'role' => 'SuperAdmin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('SuperAdmin created successfully!');
            $this->command->info('Email: superadmin@system.com');
            $this->command->info('Password: password123');
        } else {
            $this->command->info('SuperAdmin already exists!');
        }
    }
}