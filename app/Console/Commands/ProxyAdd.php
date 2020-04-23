<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Proxy;

class ProxyAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:add
                            {url : The URL of the proxy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a proxy to the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = $this->argument('url');
        $this->info('Agregando Proxy');
        $proxy = Proxy::updateOrCreate(['url' => $url], ['intentos_fallidos' => 0, 'activo' => true]);
        $this->info('Proxy agregado');

    }
}
