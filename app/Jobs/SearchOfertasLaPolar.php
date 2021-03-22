<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SearchOfertasLaPolar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $polar_tecno_70 = [
        'Tecnologia',
        'linea-blanca',
    ];

    private $polar_ropa_70 = [
        'dormitorio',
        'muebles',
        'decohogar'
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
        FunctionRataLaPolar::dispatch($this->polar_tecno_70, '70','rata.webhook_rata_tecno');
        FunctionRataLaPolar::dispatch($this->polar_ropa_70, '70','rata.webhook_rata_ropa');
    }
}
