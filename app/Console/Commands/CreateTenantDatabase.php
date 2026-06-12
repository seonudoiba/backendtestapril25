<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTenantDatabase extends Command
{
    protected $signature = 'tenant:create-database {database}';
    protected $description = 'Create a database for a tenant';

    public function handle()
    {
        $database = $this->argument('database');
        
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}`");
            $this->info("Database '{$database}' created successfully!");
            
            // Run migrations for the new tenant database
            $this->call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant'
            ]);
            
        } catch (\Exception $e) {
            $this->error("Failed to create database: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}