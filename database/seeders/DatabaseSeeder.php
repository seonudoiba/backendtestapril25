<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            SuperAdminSeeder::class,
            TenantSeeder::class,
            UserSeeder::class,
            ExpenseSeeder::class,
        ]);
    }
}