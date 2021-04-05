<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessMonitorQueueWorker;
use App\Jobs\UpdateAllCatalogs;
use App\Jobs\SearchRataFalabella;
use App\Jobs\FunctionRataFalabella;
use App\Jobs\FunctionRataLider;
use App\Jobs\SearchOfertasParis;
use Illuminate\Support\Facades\Log;
use App\Jobs\FunctionRataParis;
use App\Jobs\FunctionRataLaPolar;
use App\Jobs\SearchOfertasLaPolar;
use App\Jobs\SearchOfertasHites;

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

    private $lider_sesenta_por_ciento = [
        'Computación',
        'Tecno/Mundo Gamer',
        'Tecno/TV',
        'Celulares/Celulares y Teléfonos/Smartphones',
        'Ferretería-'
    ];

    private $lider_cincuenta_por_ciento = [
        'Electrohogar',
    ];

    private $lider_ropa_cat = [
        'Dormitorio',
        'Muebles',
        'Decohogar'
    ];

    private $paris_tecno_cincuenta = [
        'tecnologia/computadores/pc-gamer/',
        'tecnologia/computadores/notebooks/',
        'tecnologia/computadores/all-in-one+desktops+ipads+macbooks/',
        'tecnologia/computadores/notebooks-gamers/',
        'tecnologia/computadores/tablets/',
        'tecnologia/celulares/smartphone/',
        'electro/television/televisores-led/',
        'linea-blanca/refrigeracion/',
    ];

    private $paris_tecno_sesenta = [
        'electro/television/smart-tv/',
        'tecnologia/ofertas/',
        'electro/television/soundbar-home-theater/',
        'linea-blanca/lavado-secado/',
        'linea-blanca/cocina/'

    ];

    private $paris_tecno_setenta = [
        'television/televisores-oled-qled/',
        'linea-blanca/equipamiento-industrial/'

    ];

    private $paris_ropa_setenta = [
        'dormitorio/box-spring/',
        'dormitorio/camas-europeas/',
        'dormitorio/camas-americanas/',
        'muebles/living-sala-estar/tv-racks/',
        'muebles/oficina/sillas/sillas-gamer/',
        'dormitorio/muebles/closet/',
        'dormitorio/muebles/veladores/',
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
        $schedule->job(new FunctionRataFalabella($this->cincuenta_por_ciento, '50', 'rata.webhook_rata_tecno'))->everyMinute()->runInBackground();
        $schedule->job(new FunctionRataFalabella($this->sesenta_por_ciento, '60','rata.webhook_rata_tecno'))->everyMinute()->runInBackground();
        $schedule->job(new FunctionRataFalabella($this->array_parent_categories, '70', 'rata.webhook_rata_tecno'))->cron('*/2 * * * *')->runInBackground();
        $schedule->job(new FunctionRataFalabella($this->falabella_ropa_categories, '70', 'rata.webhook_rata_ropa'))->everyFiveMinutes()->runInBackground();
        $schedule->job(new FunctionRataLider($this->lider_sesenta_por_ciento, '59','rata.webhook_rata_tecno'))->everyMinute()->runInBackground();
        $schedule->job(new FunctionRataLider($this->lider_cincuenta_por_ciento, '49','rata.webhook_rata_tecno'))->everyMinute()->runInBackground();
        $schedule->job(new FunctionRataLider($this->lider_ropa_cat, '60','rata.webhook_rata_ropa'))->everyFiveMinutes()->runInBackground();
        $schedule->job(new ProcessMonitorQueueWorker)->everyMinute()->withoutOverlapping()->runInBackground();
        $schedule->job(new UpdateAllCatalogs)->everyFifteenMinutes()->runInBackground();
        $schedule->job(new SearchOfertasParis())->everyFiveMinutes()->runInBackground();
        $schedule->job(new SearchOfertasLaPolar())->everyMinute()->runInBackground();
        $schedule->job(new SearchOfertasHites())->everyMinute()->runInBackground();

        //$schedule->job(new SearchOfertasParis)->everyMinute()->runInBackground();
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
