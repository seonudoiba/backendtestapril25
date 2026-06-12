<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            // Create Admin
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                'password' => Hash::make('password123'),
                'company_id' => $company->id,
                'role' => 'Admin'
            ]);
            
            // Create Manager
            User::create([
                'name' => 'Manager User',
                'email' => 'manager@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                'password' => Hash::make('password123'),
                'company_id' => $company->id,
                'role' => 'Manager'
            ]);
            
            // Create Employee
            User::create([
                'name' => 'Employee User',
                'email' => 'employee@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                'password' => Hash::make('password123'),
                'company_id' => $company->id,
                'role' => 'Employee'
            ]);
        }
    }
}