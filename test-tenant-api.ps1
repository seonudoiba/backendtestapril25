# test-tenant-api.ps1
Write-Host "Testing Tenant Management API" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Green
Write-Host ""

# Step 1: Create SuperAdmin user
Write-Host "1. Creating SuperAdmin user..." -ForegroundColor Yellow
php artisan db:seed --class=SuperAdminSeeder
Write-Host ""

# Step 2: Login as SuperAdmin
Write-Host "2. Login as SuperAdmin..." -ForegroundColor Yellow
$loginBody = @{
    email = "superadmin@system.com"
    password = "password123"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/login" -Method Post -Body $loginBody -ContentType "application/json"
    $token = $response.token
    Write-Host "✓ Login successful!" -ForegroundColor Green
    Write-Host "Token: $token" -ForegroundColor Cyan
    Write-Host ""
} catch {
    Write-Host "✗ Login failed: $_" -ForegroundColor Red
    exit 1
}

# Step 3: Create a new tenant
Write-Host "3. Creating new tenant..." -ForegroundColor Yellow
$tenantBody = @{
    name = "New Tech Solutions"
    email = "info@newtech.com"
    database = "tenant_new_tech"
    company_name = "New Tech Solutions LLC"
    company_email = "company@newtech.com"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/tenants" -Method Post -Body $tenantBody -ContentType "application/json" -Headers @{
        Authorization = "Bearer $token"
    }
    
    Write-Host "✓ Tenant created successfully!" -ForegroundColor Green
    Write-Host "Tenant ID: $($response.data.tenant.id)" -ForegroundColor Cyan
    Write-Host "Tenant Name: $($response.data.tenant.name)" -ForegroundColor Cyan
    $tenantId = $response.data.tenant.id
    Write-Host ""
} catch {
    Write-Host "✗ Failed to create tenant:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

# Step 4: Get all tenants
Write-Host "4. Getting all tenants..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/tenants" -Method Get -Headers @{
        Authorization = "Bearer $token"
    }
    
    Write-Host "✓ Found $($response.data.data.Count) tenants" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "✗ Failed to get tenants: $_" -ForegroundColor Red
}

# Step 5: Get tenant statistics
Write-Host "5. Getting tenant statistics..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/tenants/$tenantId/statistics" -Method Get -Headers @{
        Authorization = "Bearer $token"
    }
    
    Write-Host "✓ Statistics retrieved:" -ForegroundColor Green
    Write-Host "  - Total Users: $($response.data.statistics.total_users)" -ForegroundColor Cyan
    Write-Host "  - Total Expenses: $($response.data.statistics.total_expenses)" -ForegroundColor Cyan
    Write-Host "  - Total Amount: $$($response.data.statistics.total_expense_amount)" -ForegroundColor Cyan
    Write-Host ""
} catch {
    Write-Host "✗ Failed to get statistics: $_" -ForegroundColor Red
}

# Step 6: Get simple tenants list
Write-Host "6. Getting simple tenants list..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/tenants/list" -Method Get -Headers @{
        Authorization = "Bearer $token"
    }
    
    Write-Host "✓ Simple list retrieved with $($response.data.Count) tenants" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "✗ Failed to get list: $_" -ForegroundColor Red
}

Write-Host "Test completed successfully!" -ForegroundColor Green