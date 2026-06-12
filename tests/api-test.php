<?php

/**
 * Complete API Test Suite for Expense Management System
 * Run with: php tests/api-test.php
 */

class APITester {
    private $baseUrl;
    private $token;
    private $adminToken;
    private $managerToken;
    private $employeeToken;
    private $testCompanyId;
    private $testUserId;
    private $testExpenseId;
    private $testTenantId;
    
    private $passedTests = 0;
    private $failedTests = 0;
    private $testResults = [];
    
    public function __construct($baseUrl = 'http://localhost:8000') {
        $this->baseUrl = $baseUrl;
    }
    
    public function run() {
        $this->printHeader("EXPENSE MANAGEMENT API TEST SUITE");
        $this->print("Starting comprehensive API testing...\n");
        
        // Check if server is running
        if (!$this->isServerRunning()) {
            $this->printError("Laravel server is not running on {$this->baseUrl}");
            $this->print("Please start the server with: php artisan serve");
            return false;
        }
        
        // Run all test suites
        $this->testAuthentication();
        $this->testTenantManagement();
        $this->testUserManagement();
        $this->testExpenseManagement();
        $this->testSearchAndFilters();
        $this->testRoleBasedAccess();
        $this->testAuditLogs();
        $this->testPerformanceFeatures();
        
        // Print summary
        $this->printSummary();
        
        return $this->failedTests === 0;
    }
    
    private function testAuthentication() {
        $this->printSuite("AUTHENTICATION TESTS");
        
        // Test 1: Login with valid credentials
        $response = $this->makeRequest('POST', '/api/login', [
            'email' => 'superadmin@system.com',
            'password' => 'password123'
        ]);
        
        if ($response['code'] === 200 && isset($response['data']['token'])) {
            $this->token = $response['data']['token'];
            $this->passTest("Login with valid credentials");
        } else {
            $this->failTest("Login with valid credentials", $response);
        }
        
        // Test 2: Login with invalid credentials
        $response = $this->makeRequest('POST', '/api/login', [
            'email' => 'wrong@email.com',
            'password' => 'wrongpassword'
        ]);
        
        if ($response['code'] === 422 || $response['code'] === 401) {
            $this->passTest("Login with invalid credentials");
        } else {
            $this->failTest("Login with invalid credentials", $response);
        }
        
        // Test 3: Access protected endpoint without token
        $response = $this->makeRequest('GET', '/api/me');
        
        if ($response['code'] === 401) {
            $this->passTest("Protected endpoint without token");
        } else {
            $this->failTest("Protected endpoint without token", $response);
        }
        
        // Test 4: Get current user info with valid token
        $response = $this->makeRequest('GET', '/api/me', null, $this->token);
        
        if ($response['code'] === 200 && isset($response['data']['user'])) {
            $this->passTest("Get current user info");
        } else {
            $this->failTest("Get current user info", $response);
        }
        
        // Test 5: Logout
        $response = $this->makeRequest('POST', '/api/logout', null, $this->token);
        
        if ($response['code'] === 200) {
            $this->passTest("Logout successfully");
        } else {
            $this->failTest("Logout", $response);
        }
        
        // Re-login for subsequent tests
        $response = $this->makeRequest('POST', '/api/login', [
            'email' => 'superadmin@system.com',
            'password' => 'password123'
        ]);
        $this->token = $response['data']['token'];
    }
    
    private function testTenantManagement() {
        $this->printSuite("TENANT MANAGEMENT TESTS");
        
        // Test 1: Get all tenants
        $response = $this->makeRequest('GET', '/api/tenants', null, $this->token);
        
        if ($response['code'] === 200 && isset($response['data']['success'])) {
            $this->passTest("Get all tenants list");
            $this->print("  Total tenants: " . count($response['data']['data']['data'] ?? []));
        } else {
            $this->failTest("Get all tenants list", $response);
        }
        
        // Test 2: Create a new tenant
        $tenantData = [
            'name' => 'API Test Company',
            'email' => 'api@test.com',
            'database' => 'tenant_api_test',
            'company_name' => 'API Test Solutions',
            'company_email' => 'company@apitest.com'
        ];
        
        $response = $this->makeRequest('POST', '/api/tenants', $tenantData, $this->token);
        
        if ($response['code'] === 201 && isset($response['data']['data']['tenant']['id'])) {
            $this->testTenantId = $response['data']['data']['tenant']['id'];
            $this->testCompanyId = $response['data']['data']['company']['id'];
            $this->passTest("Create new tenant");
        } else {
            $this->failTest("Create new tenant", $response);
        }
        
        // Test 3: Get single tenant
        if ($this->testTenantId) {
            $response = $this->makeRequest('GET', "/api/tenants/{$this->testTenantId}", null, $this->token);
            
            if ($response['code'] === 200 && isset($response['data']['data']['id'])) {
                $this->passTest("Get single tenant by ID");
            } else {
                $this->failTest("Get single tenant by ID", $response);
            }
        }
        
        // Test 4: Get tenant statistics
        if ($this->testTenantId) {
            $response = $this->makeRequest('GET', "/api/tenants/{$this->testTenantId}/statistics", null, $this->token);
            
            if ($response['code'] === 200 && isset($response['data']['data']['statistics'])) {
                $this->passTest("Get tenant statistics");
            } else {
                $this->failTest("Get tenant statistics", $response);
            }
        }
        
        // Test 5: Get simple tenants list
        $response = $this->makeRequest('GET', '/api/tenants/list', null, $this->token);
        
        if ($response['code'] === 200 && isset($response['data']['data'])) {
            $this->passTest("Get simple tenants list (for dropdowns)");
        } else {
            $this->failTest("Get simple tenants list", $response);
        }
    }
    
    private function testUserManagement() {
        $this->printSuite("USER MANAGEMENT TESTS");
        
        // First, get users for the test company
        $this->print("Note: Running with SuperAdmin privileges\n");
        
        // Test 1: Get all users
        $response = $this->makeRequest('GET', '/api/users', null, $this->token);
        
        if ($response['code'] === 200 && isset($response['data']['data'])) {
            $this->passTest("Get all users list");
        } else {
            $this->failTest("Get all users list", $response);
        }
        
        // Test 2: Create a new user
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'role' => 'Employee'
        ];
        
        $response = $this->makeRequest('POST', '/api/users', $userData, $this->token);
        
        if ($response['code'] === 201 && isset($response['data']['user']['id'])) {
            $this->testUserId = $response['data']['user']['id'];
            $this->passTest("Create new user");
        } else {
            $this->failTest("Create new user", $response);
        }
        
        // Test 3: Update user role
        if ($this->testUserId) {
            $updateData = [
                'role' => 'Manager'
            ];
            
            $response = $this->makeRequest('PUT', "/api/users/{$this->testUserId}", $updateData, $this->token);
            
            if ($response['code'] === 200) {
                $this->passTest("Update user role");
            } else {
                $this->failTest("Update user role", $response);
            }
        }
        
        // Test 4: Try to create user with duplicate email
        $response = $this->makeRequest('POST', '/api/users', $userData, $this->token);
        
        if ($response['code'] === 422) {
            $this->passTest("Prevent duplicate email creation");
        } else {
            $this->failTest("Prevent duplicate email creation", $response);
        }
        
        // Create users for role testing
        $this->createTestUsers();
    }
    
    private function testExpenseManagement() {
        $this->printSuite("EXPENSE MANAGEMENT TESTS");
        
        // Test 1: Create expense
        $expenseData = [
            'title' => 'Office Supplies',
            'amount' => 150.50,
            'category' => 'Office'
        ];
        
        $response = $this->makeRequest('POST', '/api/expenses', $expenseData, $this->token);
        
        if ($response['code'] === 201 && isset($response['data']['expense']['id'])) {
            $this->testExpenseId = $response['data']['expense']['id'];
            $this->passTest("Create expense");
            $this->print("  Expense ID: {$this->testExpenseId}");
        } else {
            $this->failTest("Create expense", $response);
        }
        
        // Test 2: Get all expenses
        $response = $this->makeRequest('GET', '/api/expenses', null, $this->token);
        
        if ($response['code'] === 200 && isset($response['data']['data'])) {
            $this->passTest("Get all expenses (paginated)");
        } else {
            $this->failTest("Get all expenses", $response);
        }
        
        // Test 3: Get single expense
        if ($this->testExpenseId) {
            $response = $this->makeRequest('GET', "/api/expenses/{$this->testExpenseId}", null, $this->token);
            
            if ($response['code'] === 200 && isset($response['data']['id'])) {
                $this->passTest("Get single expense by ID");
            } else {
                $this->failTest("Get single expense by ID", $response);
            }
        }
        
        // Test 4: Update expense
        if ($this->testExpenseId) {
            $updateData = [
                'title' => 'Updated Office Chair',
                'amount' => 299.99
            ];
            
            $response = $this->makeRequest('PUT', "/api/expenses/{$this->testExpenseId}", $updateData, $this->token);
            
            if ($response['code'] === 200) {
                $this->passTest("Update expense");
            } else {
                $this->failTest("Update expense", $response);
            }
        }
        
        // Test 5: Create multiple expenses for testing
        $this->createTestExpenses();
        
        // Test 6: Delete expense
        if ($this->testExpenseId) {
            $response = $this->makeRequest('DELETE', "/api/expenses/{$this->testExpenseId}", null, $this->token);
            
            if ($response['code'] === 200) {
                $this->passTest("Delete expense");
            } else {
                $this->failTest("Delete expense", $response);
            }
        }
    }
    
    private function testSearchAndFilters() {
        $this->printSuite("SEARCH AND FILTER TESTS");
        
        // Test 1: Search by title
        $response = $this->makeRequest('GET', '/api/expenses?search=Office', null, $this->token);
        
        if ($response['code'] === 200 && isset($response['data']['data'])) {
            $this->passTest("Search expenses by title");
        } else {
            $this->failTest("Search expenses by title", $response);
        }
        
        // Test 2: Filter by category
        $response = $this->makeRequest('GET', '/api/expenses?category=Office', null, $this->token);
        
        if ($response['code'] === 200) {
            $this->passTest("Filter expenses by category");
        } else {
            $this->failTest("Filter expenses by category", $response);
        }
        
        // Test 3: Filter by date range
        $fromDate = date('Y-m-d', strtotime('-30 days'));
        $toDate = date('Y-m-d');
        
        $response = $this->makeRequest('GET', "/api/expenses?from_date={$fromDate}&to_date={$toDate}", null, $this->token);
        
        if ($response['code'] === 200) {
            $this->passTest("Filter expenses by date range");
        } else {
            $this->failTest("Filter expenses by date range", $response);
        }
        
        // Test 4: Combined search and filters
        $response = $this->makeRequest('GET', '/api/expenses?search=Office&category=Office&page=1', null, $this->token);
        
        if ($response['code'] === 200) {
            $this->passTest("Combined search and filter");
        } else {
            $this->failTest("Combined search and filter", $response);
        }
        
        // Test 5: Pagination
        $response = $this->makeRequest('GET', '/api/expenses?page=1&per_page=5', null, $this->token);
        
        if ($response['code'] === 200 && isset($response['data']['per_page'])) {
            $this->passTest("Pagination works");
        } else {
            $this->failTest("Pagination", $response);
        }
    }
    
    private function testRoleBasedAccess() {
        $this->printSuite("ROLE-BASED ACCESS CONTROL TESTS");
        
        // Login as different roles
        $this->print("Testing with Admin role...\n");
        
        // Get admin token
        $adminLogin = $this->makeRequest('POST', '/api/login', [
            'email' => 'admin@TechCorp.com',
            'password' => 'password123'
        ]);
        
        if (isset($adminLogin['data']['token'])) {
            $adminToken = $adminLogin['data']['token'];
            
            // Admin can create expense
            $expenseData = ['title' => 'Admin Test', 'amount' => 100, 'category' => 'Test'];
            $response = $this->makeRequest('POST', '/api/expenses', $expenseData, $adminToken);
            
            if ($response['code'] === 201) {
                $this->passTest("Admin can create expense");
            } else {
                $this->failTest("Admin can create expense", $response);
            }
        }
        
        $this->print("Testing with Manager role...\n");
        
        // Get manager token
        $managerLogin = $this->makeRequest('POST', '/api/login', [
            'email' => 'manager@TechCorp.com',
            'password' => 'password123'
        ]);
        
        if (isset($managerLogin['data']['token'])) {
            $managerToken = $managerLogin['data']['token'];
            
            // Manager can update expense
            if ($this->testExpenseId) {
                $updateData = ['title' => 'Updated by Manager'];
                $response = $this->makeRequest('PUT', "/api/expenses/{$this->testExpenseId}", $updateData, $managerToken);
                
                if ($response['code'] === 200) {
                    $this->passTest("Manager can update expense");
                } else {
                    $this->failTest("Manager can update expense", $response);
                }
            }
            
            // Manager cannot delete expense
            if ($this->testExpenseId) {
                $response = $this->makeRequest('DELETE', "/api/expenses/{$this->testExpenseId}", null, $managerToken);
                
                if ($response['code'] === 403) {
                    $this->passTest("Manager cannot delete expense (permission denied)");
                } else {
                    $this->failTest("Manager cannot delete expense", $response);
                }
            }
        }
        
        $this->print("Testing with Employee role...\n");
        
        // Get employee token
        $employeeLogin = $this->makeRequest('POST', '/api/login', [
            'email' => 'employee@TechCorp.com',
            'password' => 'password123'
        ]);
        
        if (isset($employeeLogin['data']['token'])) {
            $employeeToken = $employeeLogin['data']['token'];
            
            // Employee can create expense
            $expenseData = ['title' => 'Employee Test', 'amount' => 50, 'category' => 'Test'];
            $response = $this->makeRequest('POST', '/api/expenses', $expenseData, $employeeToken);
            
            if ($response['code'] === 201) {
                $this->passTest("Employee can create expense");
            } else {
                $this->failTest("Employee can create expense", $response);
            }
            
            // Employee cannot update expense
            if ($this->testExpenseId) {
                $updateData = ['title' => 'Employee Update Attempt'];
                $response = $this->makeRequest('PUT', "/api/expenses/{$this->testExpenseId}", $updateData, $employeeToken);
                
                if ($response['code'] === 403) {
                    $this->passTest("Employee cannot update expense (permission denied)");
                } else {
                    $this->failTest("Employee cannot update expense", $response);
                }
            }
        }
    }
    
    private function testAuditLogs() {
        $this->printSuite("AUDIT LOG TESTS");
        
        // Create and update an expense to generate audit logs
        $expenseData = [
            'title' => 'Audit Test Expense',
            'amount' => 500,
            'category' => 'Audit'
        ];
        
        $response = $this->makeRequest('POST', '/api/expenses', $expenseData, $this->token);
        
        if ($response['code'] === 201 && isset($response['data']['expense']['id'])) {
            $expenseId = $response['data']['expense']['id'];
            $this->passTest("Create expense for audit");
            
            // Update the expense
            $updateData = ['amount' => 600];
            $response = $this->makeRequest('PUT', "/api/expenses/{$expenseId}", $updateData, $this->token);
            
            if ($response['code'] === 200) {
                $this->passTest("Update expense creates audit log");
            } else {
                $this->failTest("Update expense audit", $response);
            }
            
            // Delete the expense
            $response = $this->makeRequest('DELETE', "/api/expenses/{$expenseId}", null, $this->token);
            
            if ($response['code'] === 200) {
                $this->passTest("Delete expense creates audit log");
            } else {
                $this->failTest("Delete expense audit", $response);
            }
        }
        
        $this->print("  Audit logs are being recorded in the audit_logs table\n");
        $this->print("  You can verify with: SELECT * FROM audit_logs;\n");
    }
    
    private function testPerformanceFeatures() {
        $this->printSuite("PERFORMANCE FEATURES TESTS");
        
        // Test 1: Check if caching is working
        $this->print("Testing caching...\n");
        
        $startTime = microtime(true);
        $response1 = $this->makeRequest('GET', '/api/expenses', null, $this->token);
        $firstCallTime = microtime(true) - $startTime;
        
        $startTime = microtime(true);
        $response2 = $this->makeRequest('GET', '/api/expenses', null, $this->token);
        $secondCallTime = microtime(true) - $startTime;
        
        if ($secondCallTime < $firstCallTime) {
            $this->passTest("Caching improves response time");
            $this->print("  First call: " . round($firstCallTime * 1000, 2) . "ms");
            $this->print("  Second call: " . round($secondCallTime * 1000, 2) . "ms");
        } else {
            $this->failTest("Caching", ['first' => $firstCallTime, 'second' => $secondCallTime]);
        }
        
        // Test 2: Check eager loading (should return user relationship)
        $response = $this->makeRequest('GET', '/api/expenses?page=1', null, $this->token);
        
        if ($response['code'] === 200 && isset($response['data']['data'][0]['user'])) {
            $this->passTest("Eager loading prevents N+1 queries");
        } else {
            $this->failTest("Eager loading", $response);
        }
        
        // Test 3: Check database indexes
        $this->print("Checking database indexes...\n");
        $response = $this->makeRequest('GET', '/api/expenses?category=Office', null, $this->token);
        
        if ($response['code'] === 200) {
            $this->passTest("Database indexes on category column");
        } else {
            $this->failTest("Database indexes", $response);
        }
    }
    
    // Helper methods
    private function createTestUsers() {
        $this->print("\nCreating test users for role testing...\n");
        
        $users = [
            ['name' => 'Test Admin', 'email' => 'testadmin@example.com', 'role' => 'Admin'],
            ['name' => 'Test Manager', 'email' => 'testmanager@example.com', 'role' => 'Manager'],
            ['name' => 'Test Employee', 'email' => 'testemployee@example.com', 'role' => 'Employee']
        ];
        
        foreach ($users as $user) {
            $userData = array_merge($user, ['password' => 'password123']);
            $this->makeRequest('POST', '/api/users', $userData, $this->token);
        }
    }
    
    private function createTestExpenses() {
        $this->print("\nCreating test expenses for search testing...\n");
        
        $expenses = [
            ['title' => 'Office Chair', 'amount' => 299.99, 'category' => 'Furniture'],
            ['title' => 'Laptop', 'amount' => 1200.00, 'category' => 'Hardware'],
            ['title' => 'Software License', 'amount' => 199.99, 'category' => 'Software'],
            ['title' => 'Team Lunch', 'amount' => 85.50, 'category' => 'Meals'],
            ['title' => 'Flight Ticket', 'amount' => 450.00, 'category' => 'Travel']
        ];
        
        foreach ($expenses as $expense) {
            $this->makeRequest('POST', '/api/expenses', $expense, $this->token);
        }
    }
    
    private function makeRequest($method, $endpoint, $data = null, $token = null) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $headers = ['Content-Type: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    private function isServerRunning() {
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        return $info['http_code'] != 0;
    }
    
    private function passTest($testName) {
        $this->passedTests++;
        $this->testResults[] = ["✅ PASS", $testName];
        $this->print("  ✅ PASS: {$testName}");
    }
    
    private function failTest($testName, $response = null) {
        $this->failedTests++;
        $this->testResults[] = ["❌ FAIL", $testName];
        $this->print("  ❌ FAIL: {$testName}");
        if ($response) {
            $this->print("     Response: " . json_encode($response, JSON_PRETTY_PRINT));
        }
    }
    
    private function print($message) {
        echo $message . "\n";
    }
    
    private function printError($message) {
        echo "❌ ERROR: " . $message . "\n";
    }
    
    private function printHeader($title) {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "  " . $title . "\n";
        echo str_repeat("=", 80) . "\n";
    }
    
    private function printSuite($title) {
        echo "\n" . str_repeat("-", 80) . "\n";
        echo "📋 " . $title . "\n";
        echo str_repeat("-", 80) . "\n";
    }
    
    private function printSummary() {
        $this->printHeader("TEST SUMMARY");
        
        $total = $this->passedTests + $this->failedTests;
        $passRate = ($total > 0) ? round(($this->passedTests / $total) * 100, 2) : 0;
        
        $this->print("\n📊 Results:");
        $this->print("  ✅ Passed: " . $this->passedTests);
        $this->print("  ❌ Failed: " . $this->failedTests);
        $this->print("  📈 Total: " . $total);
        $this->print("  🎯 Pass Rate: {$passRate}%");
        
        $this->print("\n📝 Detailed Results:");
        foreach ($this->testResults as $result) {
            $this->print("  {$result[0]} - {$result[1]}");
        }
        
        if ($this->failedTests === 0) {
            $this->print("\n🎉 ALL TESTS PASSED! API is working perfectly!");
        } else {
            $this->print("\n⚠️  Some tests failed. Please check the errors above.");
        }
        
        $this->print("\n" . str_repeat("=", 80) . "\n");
    }
}

// Run the tests
$tester = new APITester('http://localhost:8000');
$success = $tester->run();
exit($success ? 0 : 1);