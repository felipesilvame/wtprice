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

    private $paris_tecno_70 = [
        'tecnologia/computadores/',
        'tecnologia/gamers/',
        'tecnologia/consolas-videojuegos/',
        'tecnologia/ofertas/',
        'tecnologia/celulares/smartphone/',
        'electro/elige-tu-pulgada',
        'electro/television/',
        'electro/television/soundbar-home-theater/',
        'television/televisores-oled-qled/',
        'linea-blanca/refrigeracion/',
        'linea-blanca/lavado-secado/',
        'linea-blanca/cocina/',
        'linea-blanca/electrodomesticos/',
    ];


    private $paris_ropa_setenta = [
        'decohogar/decoracion',
        'decohogar/menaje-cocina',
        'decohogar/menaje-mesa',
        'decohogar/iluminacion',
        'decohogar/ofertas',
        'dormitorio',
        'muebles',
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
        \App\Jobs\FunctionRataParis::dispatch($this->paris_tecno_70, '70','rata.webhook_rata_ropa');
        \App\Jobs\FunctionRataParis::dispatch($this->paris_ropa_setenta, '70','rata.webhook_rata_ropa');
    }
}
