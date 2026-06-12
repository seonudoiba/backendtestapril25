<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Company;
use App\Models\User;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        // Only super admin can access this
        if ($request->user()->role !== 'SuperAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can access tenants.'
            ], 403);
        }
        
        try {
            $tenants = Tenant::with('company')->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $tenants
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tenants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tenants'
            ], 500);
        }
    }
    
    public function statistics(Request $request, $id)
    {
        // Only super admin can access tenant statistics
        if ($request->user()->role !== 'SuperAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can view tenant statistics.'
            ], 403);
        }
        
        try {
            $tenant = Tenant::with('company')->findOrFail($id);
            $company = $tenant->company;
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found for this tenant'
                ], 404);
            }
            
            // Get statistics for this tenant's company
            $totalUsers = User::where('company_id', $company->id)->count();
            $totalExpenses = Expense::where('company_id', $company->id)->count();
            $totalAmount = Expense::where('company_id', $company->id)->sum('amount');
            
            // Get expenses by category
            $expensesByCategory = Expense::where('company_id', $company->id)
                ->select('category', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
                ->groupBy('category')
                ->get();
            
            // Get recent expenses
            $recentExpenses = Expense::where('company_id', $company->id)
                ->with('user')
                ->latest()
                ->limit(10)
                ->get();
            
            // Get user statistics by role
            $usersByRole = User::where('company_id', $company->id)
                ->select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get();
            
            // Get monthly expenses for the last 6 months
            $monthlyExpenses = Expense::where('company_id', $company->id)
                ->where('created_at', '>=', now()->subMonths(6))
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('count(*) as count'),
                    DB::raw('sum(amount) as total')
                )
                ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                ->orderBy('month', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'email' => $tenant->email,
                        'created_at' => $tenant->created_at,
                    ],
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'email' => $company->email,
                        'created_at' => $company->created_at,
                    ],
                    'statistics' => [
                        'total_users' => $totalUsers,
                        'total_expenses' => $totalExpenses,
                        'total_amount' => $totalAmount,
                        'average_expense' => $totalExpenses > 0 ? $totalAmount / $totalExpenses : 0,
                    ],
                    'breakdown' => [
                        'expenses_by_category' => $expensesByCategory,
                        'users_by_role' => $usersByRole,
                        'monthly_expenses' => $monthlyExpenses,
                        'recent_expenses' => $recentExpenses,
                    ]
                ]
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching tenant statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tenant statistics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        // Only super admin can create tenants
        if ($request->user()->role !== 'SuperAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can create tenants.'
            ], 403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'database' => 'required|string|unique:tenants,database',
        ]);
        
        try {
            $tenant = null;
            DB::transaction(function () use ($request, &$tenant) {
                // Create tenant with UUID
                $tenant = Tenant::create([
                    'id' => (string) Str::uuid(),
                    'name' => $request->name,
                    'email' => $request->email,
                    'database' => $request->database,
                    'settings' => $request->settings ?? [],
                ]);
                
                // Create company for this tenant
                Company::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'tenant_id' => $tenant->id,
                ]);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Tenant created successfully',
                'data' => $tenant
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating tenant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tenant: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show(Request $request, $id)
    {
        // Only super admin can view tenant details
        if ($request->user()->role !== 'SuperAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can view tenants.'
            ], 403);
        }
        
        try {
            $tenant = Tenant::with('company')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $tenant
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tenant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }
    }
    
    public function update(Request $request, $id)
    {
        // Only super admin can update tenants
        if ($request->user()->role !== 'SuperAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can update tenants.'
            ], 403);
        }
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:tenants,email,' . $id,
            'settings' => 'sometimes|array',
        ]);
        
        try {
            $tenant = Tenant::findOrFail($id);
            $tenant->update($request->only(['name', 'email', 'settings']));
            
            // Update associated company if name changed
            if ($request->has('name') && $tenant->company) {
                $tenant->company->update(['name' => $request->name]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Tenant updated successfully',
                'data' => $tenant->load('company')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating tenant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tenant'
            ], 500);
        }
    }
    
    public function destroy(Request $request, $id)
    {
        // Only super admin can delete tenants
        if ($request->user()->role !== 'SuperAdmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Super Admin can delete tenants.'
            ], 403);
        }
        
        try {
            $tenant = Tenant::findOrFail($id);
            
            // Delete associated company first
            if ($tenant->company) {
                $tenant->company->delete();
            }
            
            $tenant->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Tenant deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting tenant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tenant'
            ], 500);
        }
    }
}