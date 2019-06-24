<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
        \App\Jobs\UpdateCatalogAbcdin::dispatch();
        \App\Jobs\UpdateCatalogCorona::dispatch();
        \App\Jobs\UpdateCatalogFalabella::dispatch();
        \App\Jobs\UpdateCatalogJumbo::dispatch();
        \App\Jobs\UpdateCatalogLider::dispatch();
        \App\Jobs\UpdateCatalogRipley::dispatch();
    }
}
