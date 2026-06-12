<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Run weekly expense report every Monday at 9 AM
        $schedule->job(new \App\Jobs\SendWeeklyExpenseReport())
                 ->weekly()
                 ->mondays()
                 ->at('09:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}