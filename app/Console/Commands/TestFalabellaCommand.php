<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        $page_start = 1;
        $url = 'https://www.falabella.com/s/browse/v1/listing/cl';
        $url .= "?categoryId=$category&page=$page_start&zone=13&channel=app&sortBy=product.attribute.newIconExpiryDate,desc&f.range.derived.variant.discount=$_d%25dcto+y+más&f.derived.variant.sellerId=FALABELLA%3A%3ASODIMAC%3A%3ATOTTUS";
        $response = null;
        $data = null;
        $total_pages = 0;
        $options = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9',
                'Accept'     => 'application/json',
            ]
        ];
        if ((bool)env('APP_PROXY') && (bool)env('APP_PROXY_FALABELLA')) {
            $options['proxy'] = env('APP_PROXY');
            $options['verify'] = false;
            $options['timeout'] = 15;
        }
        try {
            //FALABELLA BLOCKS THE F*KING REQUESTS!!!!
            usleep(1400000);
            if (1) {
                //deprecated, using classic curl
                $response = $client->get($url, $options)->getBody()->getContents();
            }
        } catch (\Exception $e) {
            Log::warning("SearchRataFalabella: No se ha obtenido respuesta satisfactoria de parte del request" . $tienda->nombre);
            throw $e;
        }
        if ((boolean) $response) {
            return 'Success';
        } else return 'Failed';

    }
}
