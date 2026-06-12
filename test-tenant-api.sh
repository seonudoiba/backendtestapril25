#!/bin/bash

echo "Testing Tenant Management API"
echo "================================"
echo ""

# Step 1: Run migrations and seeders
echo "1. Setting up database..."
php artisan migrate:fresh
php artisan db:seed --class=SuperAdminSeeder
php artisan db:seed --class=TenantSeeder
php artisan db:seed --class=UserSeeder

echo ""
echo "2. Login as SuperAdmin..."

# Login and capture response
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"superadmin@system.com","password":"password123"}')

# Extract token using grep and cut (since jq is not available)
TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "❌ Login failed!"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
fi

echo "✅ Login successful!"
echo "Token: ${TOKEN:0:50}..."
echo ""

# Step 3: Create a new tenant
echo "3. Creating a new tenant..."

CREATE_RESPONSE=$(curl -s -X POST http://localhost:8000/api/tenants \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Tech Solutions",
    "email": "info@newtech.com",
    "database": "tenant_new_tech",
    "company_name": "New Tech Solutions LLC",
    "company_email": "company@newtech.com"
  }')

echo "Response: $CREATE_RESPONSE"
echo ""

# Check if creation was successful
if echo "$CREATE_RESPONSE" | grep -q '"success":true'; then
    echo "✅ Tenant created successfully!"
    # Extract tenant ID
    TENANT_ID=$(echo "$CREATE_RESPONSE" | grep -o '"id":"[^"]*' | head -1 | cut -d'"' -f4)
    echo "Tenant ID: $TENANT_ID"
else
    echo "❌ Failed to create tenant"
fi

echo ""

# Step 4: Get all tenants
echo "4. Getting all tenants..."
ALL_TENANTS=$(curl -s -X GET "http://localhost:8000/api/tenants" \
  -H "Authorization: Bearer $TOKEN")

echo "$ALL_TENANTS" | head -c 500
echo "..."
echo ""

# Step 5: Get simple tenants list
echo "5. Getting simple tenants list..."
SIMPLE_LIST=$(curl -s -X GET "http://localhost:8000/api/tenants/list" \
  -H "Authorization: Bearer $TOKEN")

echo "$SIMPLE_LIST"
echo ""

echo "✅ Test completed!"