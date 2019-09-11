<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessMonitorQueueWorker;
use App\Jobs\UpdateAllCatalogs;
use Illuminate\Support\Facades\Log;

/**
 * Class Kernel.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new ProcessMonitorQueueWorker)->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->job(new UpdateAllCatalogs)->dailyAt('01:58')->runInBackground();
        $schedule->job(new UpdateAllCatalogs)->dailyAt('08:08')->runInBackground();
        $schedule->job(new UpdateAllCatalogs)->dailyAt('14:11')->runInBackground();
        $schedule->job(new UpdateAllCatalogs)->dailyAt('20:44')->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
