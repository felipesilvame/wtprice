<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SearchOfertasParis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \App\Jobs\FunctionRataParis::dispatch($this->paris_tecno_cincuenta, '50','rata.webhook_rata_tecno');
        \App\Jobs\FunctionRataParis::dispatch($this->paris_tecno_sesenta, '60','rata.webhook_rata_tecno');
        \App\Jobs\FunctionRataParis::dispatch($this->paris_tecno_setenta, '69','rata.webhook_rata_tecno');
        \App\Jobs\FunctionRataParis::dispatch($this->paris_ropa_setenta, '70','rata.webhook_rata_ropa');
    }
}
