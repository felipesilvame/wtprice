<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Jobs\UpdateCatalogAbcdin;
use App\Jobs\UpdateCatalogCorona;
use App\Jobs\UpdateCatalogFalabella;
use App\Jobs\UpdateCatalogJumbo;
use App\Jobs\UpdateCatalogLider;
use App\Jobs\UpdateCatalogRipley;
use App\Jobs\UpdateCatalogParis;
use App\Jobs\UpdateCatalogLaPolar;
use App\Jobs\UpdateCatalogHites;

class UpdateAllCatalogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        //dispatches catalogs
        UpdateCatalogAbcdin::dispatch();
        UpdateCatalogCorona::dispatch();
        UpdateCatalogFalabella::dispatch()->onQueue('falabella');
        UpdateCatalogJumbo::dispatch();
        UpdateCatalogLider::dispatch();
        UpdateCatalogRipley::dispatch()->onQueue('ripley');
        UpdateCatalogParis::dispatch();
        UpdateCatalogLaPolar::dispatch();
        UpdateCatalogHites::dispatch();
    }
}
