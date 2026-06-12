<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Company;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $tenants = [
            [
                'id' => (string) Str::uuid(),
                'name' => 'Tech Corp', 
                'email' => 'tech@corp.com', 
                'database' => 'tenant_tech_corp'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Health Inc', 
                'email' => 'health@inc.com', 
                'database' => 'tenant_health_inc'
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Finance Ltd', 
                'email' => 'finance@ltd.com', 
                'database' => 'tenant_finance_ltd'
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::create($tenantData);
            
            // Create company for this tenant
            Company::create([
                'name' => $tenantData['name'],
                'email' => $tenantData['email'],
                'tenant_id' => $tenant->id,
            ]);
        }
    }
}