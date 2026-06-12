<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyExpenseReport extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $expenses;
    public $totalAmount;

    public function __construct($company, $expenses, $totalAmount)
    {
        $this->company = $company;
        $this->expenses = $expenses;
        $this->totalAmount = $totalAmount;
    }

    public function build()
    {
        return $this->subject('Weekly Expense Report - ' . $this->company->name)
                    ->view('emails.weekly-expense-report');
    }
}