<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\Proxy as ProxyModel;
use App\Helpers\General\Proxy;
use App\Helpers\General\Arr as ArrHelper;
use Exception;

class TestProxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:test
                            {url : the url of the proxy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test a proxy for ripley and add it';

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
        $proxy = $this->argument('url');
        if(!$proxy) throw new Exception("Debe haber un proxy para testear", 1);
        $this->info('Iniciando test...');
        $client = new \GuzzleHttp\Client();
        $tienda = Tienda::where('nombre', 'Ripley')->first();
        if (!$tienda) throw new Exception("No existe la tienda Ripley", 1);
        $product = $tienda->productos()->where('estado', 'Activo')->orderBy('updated_at', 'DESC')->first();
        if (!$product) throw new Exception("No hay producto para hacer la prueba", 1);
        $url = '';
        if ($tienda->protocolo) {
            $url .= $tienda->protocolo."://";
        }
        if ($tienda->prefix_api) {
            $url .= $tienda->prefix_api;
        }
        if ($tienda->method == "GET") {
            $url .= $product->sku;
        } else if ($tienda->method == "POST" && !$tienda->request_body_sku) {
            $url .= $product->sku;
        }
        if ($tienda->suffix_api) {
            $url .= $tienda->suffix_api;
        }
        $options = [];
        $options['proxy'] = $proxy;
        $options['verify'] = false;
        $options['timeout'] = 15;
        $request = null;
        $response = null;
        try {
            $request = new \GuzzleHttp\Psr7\Request($tienda->method, $url);
            $res = $client->send($request, $options);
            $response = (string) $res->getBody();
        } catch (Exception $e) {
            throw $e;
        }
        // success
        $this->info("Success!");
        if ((boolean) $response){
            $data = null;
            try {
              $data = json_decode($response, true);
            } catch (\Exception $e) {
              $this->warning("Producto id ".$product->id.": No se ha podido convertir la respuesta a JSON");
              throw $e;
              
            }
            $this->info('Agregando Proxy');
            $proxy = Proxy::updateOrCreate(['url' => $url], ['intentos_fallidos' => 0, 'activo' => true]);
            $this->info('Proxy agregado');
        }
        
    }
}
