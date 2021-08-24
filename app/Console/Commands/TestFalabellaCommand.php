<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestFalabellaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:falabella';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client();
	$tienda = null;
	$total_pages = 0;
	$tienda = \App\Models\Tienda::whereNombre('Falabella')->first();
	$_d = (string)'40';
	$category = 'cat7090034';
	$uri = 'https://www.falabella.com/s/browse/v1/listing/cl';
	
    }
}
