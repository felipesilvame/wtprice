<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessMonitorQueueWorker;
use App\Jobs\UpdateAllCatalogs;
use App\Jobs\SearchRataFalabella;
use App\Jobs\FunctionRataFalabella;
use Illuminate\Support\Facades\Log;

/**
 * Class Kernel.
 */
class Kernel extends ConsoleKernel
{
    private $array_parent_categories = [
        'cat7090034', // Tecnología
        'cat16400010', // Telefonía
        'cat16510006', // Electrohogar
    ];

    private $falabella_ropa_categories = [
        'cat8950017', // Decohogar
        'cat1008', // Muebles
        'cat1005', // Dormitorio
        'cat6930002', // Deportes
        'cat7330051', // Moda Mujer
        'cat7450065', // Moda Hombre
    ];

    private $cincuenta_por_ciento = [
        'cat70057', //Notebooks,
        
    ];

    private $sesenta_por_ciento = [
        'cat7190148', //Televisores LED
        'cat3770004', //Consolas
    ];

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
        $schedule->job(new FunctionRataFalabella($this->cincuenta_por_ciento, '50', config('rata.webhook_rata_tecno')))->everyMinute()->runInBackground();
        $schedule->job(new FunctionRataFalabella($this->sesenta_por_ciento, '60', config('rata.webhook_rata_tecno')))->everyMinute()->runInBackground();
        $schedule->job(new FunctionRataFalabella($this->array_parent_categories, '70', config('rata.webhook_rata_tecno')))->cron('*/3 * * * *')->runInBackground();
        $schedule->job(new FunctionRataFalabella($this->falabella_ropa_categories, '70', config('rata.webhook_rata_ropa')))->everyFiveMinutes()->runInBackground();
        $schedule->job(new ProcessMonitorQueueWorker)->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->job(new UpdateAllCatalogs)->everyFifteenMinutes()->runInBackground();
        //$schedule->job(new UpdateAllCatalogs)->dailyAt('01:58')->runInBackground();
        //$schedule->job(new UpdateAllCatalogs)->dailyAt('08:08')->runInBackground();
        //$schedule->job(new UpdateAllCatalogs)->dailyAt('14:11')->runInBackground();
        //$schedule->job(new UpdateAllCatalogs)->dailyAt('20:44')->runInBackground();
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
