# 🧾 Multi-Tenant SaaS-Based Expense Management API

A secure, high-performance API for managing expenses across multiple companies with complete data isolation.

## Features

- **Multi-Tenant Support** - Complete data isolation between companies
- **Secure API Authentication** - Laravel Sanctum token-based authentication
- **Role-Based Access Control (RBAC)** - Admin, Manager, Employee roles
- **Advanced Query Optimization** - Indexing, Eager Loading, Redis caching
- **Background Job Processing** - Laravel Queues for weekly reports
- **Audit Logging** - Track all changes to expenses

## Tech Stack

- Laravel 13
- MySQL
- Laravel Sanctum
- Redis (Caching & Queue)
- Spatie Laravel Multitenancy

## API Endpoints

### Authentication
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user
- `GET /api/me` - Get authenticated user

### Expense Management
- `GET /api/expenses` - List expenses (with search & filters)
- `POST /api/expenses` - Create expense
- `PUT /api/expenses/{id}` - Update expense (Admin/Manager)
- `DELETE /api/expenses/{id}` - Delete expense (Admin only)
- `GET /api/expenses/report/summary` - Get expense summary

### User Management (Admin only)
- `GET /api/users` - List company users
- `POST /api/users` - Create user
- `PUT /api/users/{id}` - Update user role
- `POST /api/register` - Register new user

### Tenant Management (Super Admin only)
- `GET /api/tenants` - List all tenants
- `POST /api/tenants` - Create tenant
- `GET /api/tenants/{id}` - Get tenant details
- `PUT /api/tenants/{id}` - Update tenant
- `DELETE /api/tenants/{id}` - Delete tenant
- `GET /api/tenants/{id}/statistics` - Get tenant statistics

## Installation

### Prerequisites
- PHP 8.1+
- Composer
- MySQL/PostgreSQL
- Redis (optional)

### Setup Steps

```bash
# Clone repository
git clone https://github.com/seonudoiba/backendtestapril25.git
cd backendtestapril25

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_management
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Install Sanctum
php artisan vendor:publish --tag=sanctum-migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Start queue worker
php artisan queue:work

# Start development server
php artisan serve
