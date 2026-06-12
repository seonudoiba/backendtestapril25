<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\User;
use App\Mail\WeeklyExpenseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SendWeeklyExpenseReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Get all companies
        $companies = Company::with(['users' => function($query) {
            $query->where('role', 'Admin');
        }])->get();

        foreach ($companies as $company) {
            // Get last week's expenses for this company
            $expenses = DB::table('expenses')
                ->where('company_id', $company->id)
                ->whereBetween('created_at', [now()->subWeek(), now()])
                ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('category')
                ->get();

            $totalAmount = $expenses->sum('total');

            // Send email to each admin
            foreach ($company->users as $admin) {
                Mail::to($admin->email)->send(new WeeklyExpenseReport($company, $expenses, $totalAmount));
            }
        }
    }
}