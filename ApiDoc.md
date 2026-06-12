```markdown
# 🧾 Multi-Tenant SaaS-Based Expense Management API - Complete Documentation

## 📋 API Base URL
```
http://localhost:8000/api
```

## 🔐 Authentication Endpoints

### 1. Login User
**POST** `/api/login`

**Request Body:**
```json
{
    "email": "admin@TechCorp.com",
    "password": "password123"
}
```

**Response (200 OK):**
```json
{
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@TechCorp.com",
        "role": "Admin",
        "company_id": 1
    },
    "token": "1|abcdef1234567890",
    "role": "Admin"
}
```

### 2. Logout User
**POST** `/api/logout`

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
    "message": "Logged out successfully"
}
```

### 3. Get Current User
**GET** `/api/me`

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
    "id": 1,
    "name": "Admin User",
    "email": "admin@TechCorp.com",
    "role": "Admin",
    "company_id": 1,
    "company": {
        "id": 1,
        "name": "Tech Corp",
        "email": "tech@corp.com"
    }
}
```

### 4. Register New User (Admin Only)
**POST** `/api/register`

**Headers:** `Authorization: Bearer {admin_token}`

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@techcorp.com",
    "password": "securepassword123",
    "role": "Employee"
}
```

**Response (201 Created):**
```json
{
    "message": "User created successfully",
    "user": {
        "id": 4,
        "name": "John Doe",
        "email": "john@techcorp.com",
        "role": "Employee",
        "company_id": 1
    }
}
```

---

## 💰 Expense Management Endpoints

### 5. List All Expenses
**GET** `/api/expenses`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| page | integer | Page number |
| per_page | integer | Items per page (default: 20) |
| search | string | Search by title/category |
| category | string | Filter by category |
| from_date | date | Start date (Y-m-d) |
| to_date | date | End date (Y-m-d) |

**Example Request:**
```
GET /api/expenses?page=1&per_page=10&search=office&category=Supplies&from_date=2024-01-01&to_date=2024-01-31
```

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Office Supplies",
                "amount": 150.50,
                "category": "Office",
                "created_at": "2024-01-15T10:30:00.000000Z",
                "user": {
                    "id": 1,
                    "name": "Admin User",
                    "email": "admin@TechCorp.com"
                }
            }
        ],
        "total": 95,
        "per_page": 20,
        "last_page": 5
    }
}
```

### 6. Create Expense
**POST** `/api/expenses`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "title": "MacBook Pro M3",
    "amount": 1999.99,
    "category": "Hardware"
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Expense created successfully",
    "data": {
        "id": 3,
        "title": "MacBook Pro M3",
        "amount": 1999.99,
        "category": "Hardware",
        "company_id": 1,
        "user_id": 1,
        "created_at": "2024-01-15T14:20:00.000000Z",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@TechCorp.com"
        }
    }
}
```

### 7. Update Expense (Admin/Manager Only)
**PUT** `/api/expenses/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
    "title": "MacBook Pro M3 Pro",
    "amount": 2499.99,
    "category": "Hardware"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Expense updated successfully",
    "data": {
        "id": 3,
        "title": "MacBook Pro M3 Pro",
        "amount": 2499.99,
        "category": "Hardware",
        "updated_at": "2024-01-15T14:25:00.000000Z"
    }
}
```

### 8. Delete Expense (Admin Only)
**DELETE** `/api/expenses/{id}`

**Headers:** `Authorization: Bearer {admin_token}`

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Expense deleted successfully"
}
```

### 9. Expense Summary Report (Admin/Manager Only)
**GET** `/api/expenses/report/summary`

**Headers:** `Authorization: Bearer {token}`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "total_expenses": 95,
        "total_amount": 45750.75,
        "average_amount": 481.59,
        "by_category": [
            {
                "category": "Hardware",
                "count": 15,
                "total": 15000.00
            },
            {
                "category": "Software",
                "count": 30,
                "total": 12000.00
            }
        ],
        "recent_expenses": [
            {
                "id": 95,
                "title": "Latest Expense",
                "amount": 150.00,
                "category": "Office"
            }
        ]
    }
}
```

---

## 👥 User Management Endpoints (Admin Only)

### 10. List All Users
**GET** `/api/users`

**Headers:** `Authorization: Bearer {admin_token}`

**Response (200 OK):**
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "name": "Admin User",
            "email": "admin@TechCorp.com",
            "role": "Admin",
            "company_id": 1,
            "created_at": "2024-01-15T10:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "Manager User",
            "email": "manager@TechCorp.com",
            "role": "Manager",
            "company_id": 1
        }
    ],
    "total": 3
}
```

### 11. Create User
**POST** `/api/users`

**Headers:** `Authorization: Bearer {admin_token}`

**Request Body:**
```json
{
    "name": "Sarah Johnson",
    "email": "sarah@techcorp.com",
    "password": "password123",
    "role": "Manager"
}
```

**Response (201 Created):**
```json
{
    "message": "User created successfully",
    "user": {
        "id": 4,
        "name": "Sarah Johnson",
        "email": "sarah@techcorp.com",
        "role": "Manager",
        "company_id": 1
    }
}
```

### 12. Update User Role
**PUT** `/api/users/{id}`

**Headers:** `Authorization: Bearer {admin_token}`

**Request Body:**
```json
{
    "role": "Admin",
    "name": "Sarah Johnson Updated"
}
```

**Response (200 OK):**
```json
{
    "message": "User updated successfully",
    "user": {
        "id": 4,
        "name": "Sarah Johnson Updated",
        "email": "sarah@techcorp.com",
        "role": "Admin",
        "company_id": 1
    }
}
```

### 13. Delete User
**DELETE** `/api/users/{id}`

**Headers:** `Authorization: Bearer {admin_token}`

**Response (200 OK):**
```json
{
    "message": "User deleted successfully"
}
```

---

## 🏢 Tenant Management Endpoints (Super Admin Only)

### 14. List All Tenants
**GET** `/api/tenants`

**Headers:** `Authorization: Bearer {superadmin_token}`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "550e8400-e29b-41d4-a716-446655440000",
                "name": "Tech Corp",
                "email": "tech@corp.com",
                "database": "tenant_tech_corp",
                "created_at": "2024-01-15T10:00:00.000000Z",
                "company": {
                    "id": 1,
                    "name": "Tech Corp"
                }
            }
        ],
        "total": 3
    }
}
```

### 15. Create Tenant
**POST** `/api/tenants`

**Headers:** `Authorization: Bearer {superadmin_token}`

**Request Body:**
```json
{
    "name": "New Company Ltd",
    "email": "info@newcompany.com",
    "database": "tenant_new_company",
    "settings": {
        "timezone": "UTC",
        "currency": "USD"
    }
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Tenant created successfully",
    "data": {
        "id": "660e8400-e29b-41d4-a716-446655440001",
        "name": "New Company Ltd",
        "email": "info@newcompany.com",
        "database": "tenant_new_company",
        "created_at": "2024-01-15T17:00:00.000000Z"
    }
}
```

### 16. Get Tenant Statistics
**GET** `/api/tenants/{id}/statistics`

**Headers:** `Authorization: Bearer {superadmin_token}`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "tenant": {
            "id": "550e8400-e29b-41d4-a716-446655440000",
            "name": "Tech Corp",
            "email": "tech@corp.com"
        },
        "statistics": {
            "total_users": 15,
            "total_expenses": 95,
            "total_amount": 45750.75,
            "average_expense": 481.59
        },
        "breakdown": {
            "expenses_by_category": [
                {
                    "category": "Hardware",
                    "count": 15,
                    "total": 15000.00
                }
            ],
            "users_by_role": [
                {
                    "role": "Admin",
                    "count": 1
                },
                {
                    "role": "Manager",
                    "count": 3
                },
                {
                    "role": "Employee",
                    "count": 11
                }
            ]
        }
    }
}
```

### 17. Get Single Tenant
**GET** `/api/tenants/{id}`

**Headers:** `Authorization: Bearer {superadmin_token}`

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Tech Corp",
        "email": "tech@corp.com",
        "database": "tenant_tech_corp",
        "created_at": "2024-01-15T10:00:00.000000Z",
        "company": {
            "id": 1,
            "name": "Tech Corp"
        }
    }
}
```

### 18. Update Tenant
**PUT** `/api/tenants/{id}`

**Headers:** `Authorization: Bearer {superadmin_token}`

**Request Body:**
```json
{
    "name": "Tech Corp Updated",
    "email": "updated@techcorp.com",
    "settings": {
        "timezone": "America/New_York"
    }
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Tenant updated successfully",
    "data": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Tech Corp Updated",
        "email": "updated@techcorp.com"
    }
}
```

### 19. Delete Tenant
**DELETE** `/api/tenants/{id}`

**Headers:** `Authorization: Bearer {superadmin_token}`

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Tenant deleted successfully"
}
```

---

## 🔒 Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created successfully |
| 401 | Unauthenticated |
| 403 | Forbidden - Insufficient permissions |
| 404 | Resource not found |
| 422 | Validation error |
| 500 | Server error |

**Error Response Example (403 Forbidden):**
```json
{
    "message": "Unauthorized - Insufficient permissions",
    "required_roles": ["Admin"],
    "user_role": "Employee"
}
```

**Error Response Example (422 Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."],
        "amount": ["The amount must be at least 0."]
    }
}
```

---

## 🧪 Testing with cURL

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@TechCorp.com","password":"password123"}'

# Create expense
curl -X POST http://localhost:8000/api/expenses \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Expense","amount":100.00,"category":"Testing"}'

# Get expenses with filters
curl -X GET "http://localhost:8000/api/expenses?search=test&category=Testing&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📊 Default Database Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@system.com | password123 |
| Admin | admin@TechCorp.com | password123 |
| Manager | manager@TechCorp.com | password123 |
| Employee | employee@TechCorp.com | password123 |

---

**API Version:** 1.0.0  
**Last Updated:** January 2026  
**Built With:** Laravel 11
EOF