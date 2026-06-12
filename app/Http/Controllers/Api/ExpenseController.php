<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;
            $cacheKey = "expenses_{$companyId}_page_{$request->get('page', 1)}_search_{$request->search}_category_{$request->category}";
            
            $expenses = Cache::remember($cacheKey, 300, function () use ($request, $companyId) {
                $query = Expense::with(['user'])
                    ->where('company_id', $companyId);
                
                // Search functionality
                if ($request->has('search') && $request->search) {
                    $query->where(function ($q) use ($request) {
                        $q->where('title', 'like', '%' . $request->search . '%')
                          ->orWhere('category', 'like', '%' . $request->search . '%');
                    });
                }
                
                // Filter by category
                if ($request->has('category') && $request->category) {
                    $query->where('category', $request->category);
                }
                
                // Filter by date range
                if ($request->has('from_date')) {
                    $query->whereDate('created_at', '>=', $request->from_date);
                }
                
                if ($request->has('to_date')) {
                    $query->whereDate('created_at', '<=', $request->to_date);
                }
                
                return $query->latest()->paginate($request->get('per_page', 20));
            });
            
            return response()->json([
                'success' => true,
                'data' => $expenses
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching expenses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expenses'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
        ]);

        try {
            $expense = DB::transaction(function () use ($request) {
                $expense = Expense::create([
                    'company_id' => $request->user()->company_id,
                    'user_id' => $request->user()->id,
                    'title' => $request->title,
                    'amount' => $request->amount,
                    'category' => $request->category,
                ]);
                
                // Clear cache for this company's expenses
                Cache::forget("expenses_{$request->user()->company_id}_*");
                
                return $expense;
            });

            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully',
                'data' => $expense->load('user')
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating expense: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create expense'
            ], 500);
        }
    }

    public function update(Request $request, Expense $expense)
    {
        // Check if user belongs to same company
        if ($expense->company_id !== $request->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Expense belongs to different company'
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|string|max:100',
        ]);

        try {
            $oldValues = $expense->toArray();
            
            DB::transaction(function () use ($request, $expense, $oldValues) {
                $expense->update($request->only(['title', 'amount', 'category']));
                
                // Log audit
                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'company_id' => $expense->company_id,
                    'action' => 'UPDATE',
                    'model_type' => Expense::class,
                    'model_id' => $expense->id,
                    'old_values' => $oldValues,
                    'new_values' => $expense->toArray(),
                ]);
                
                // Clear cache
                Cache::forget("expenses_{$request->user()->company_id}_*");
            });

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
                'data' => $expense->fresh('user')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating expense: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update expense'
            ], 500);
        }
    }

    public function destroy(Request $request, Expense $expense)
    {
        // Check if user belongs to same company
        if ($expense->company_id !== $request->user()->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Expense belongs to different company'
            ], 403);
        }

        try {
            DB::transaction(function () use ($request, $expense) {
                // Log audit before deletion
                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'company_id' => $expense->company_id,
                    'action' => 'DELETE',
                    'model_type' => Expense::class,
                    'model_id' => $expense->id,
                    'old_values' => $expense->toArray(),
                    'new_values' => null,
                ]);
                
                $expense->delete();
                
                // Clear cache
                Cache::forget("expenses_{$request->user()->company_id}_*");
            });

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting expense: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete expense'
            ], 500);
        }
    }

    public function summary(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;
            $cacheKey = "expenses_summary_{$companyId}";
            
            $summary = Cache::remember($cacheKey, 3600, function () use ($companyId) {
                return [
                    'total_expenses' => Expense::where('company_id', $companyId)->count(),
                    'total_amount' => Expense::where('company_id', $companyId)->sum('amount'),
                    'average_amount' => Expense::where('company_id', $companyId)->avg('amount'),
                    'by_category' => Expense::where('company_id', $companyId)
                        ->select('category', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
                        ->groupBy('category')
                        ->get(),
                    'recent_expenses' => Expense::where('company_id', $companyId)
                        ->with('user')
                        ->latest()
                        ->limit(10)
                        ->get()
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching expense summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch expense summary'
            ], 500);
        }
    }
}