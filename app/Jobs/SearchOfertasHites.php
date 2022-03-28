<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\FunctionRataHites;

class SearchOfertasHites implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $hites_tecno_70 = [
        'tecnologia',
        'electrohogar',
        'telefonia',
        'smartphones',
        'herramientas-menu',
    ];

    private $hites_ropa_70 = [
        'dormitorio',
        'muebles',
        'decohogar',
        'hogar',
        'hombre',
        'mujer',
        'liquidacion',
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
        FunctionRataHites::dispatch($this->hites_tecno_70, '70','rata.webhook_rata_tecno');
        FunctionRataHites::dispatch($this->hites_ropa_70, '70','rata.webhook_rata_ropa');
    }
}
