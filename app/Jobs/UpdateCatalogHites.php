<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\General\Arr as ArrHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr; 

class UpdateCatalogHites implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $categories;
    private $method;
    private $protocol;
    private $uri;
    private $page_start;
    private $tienda;
    private $total_pages_field;
    private $results_field;
    private $sku_field;
    private $current_page_field;
    private $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->categories = [
        'tvvideo',
        'computacion',
        'consolas',
        'juegos',
        'suscripciones',
        'playstation',
        'xbox',
        'nintendo',
        'camarasreflex',
        'camarassemiprofesional',
        'camarasdeportivas',
        'smartphones',

      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'www.hites.com/on/demandware.store/Sites-HITES-Site/default/Search-UpdateGrid';
      $this->page_start = 0;
      $this->total_pages = 0;
      $this->tienda = null;
      $this->total_pages_field = null;
      $this->results_field = null;
      $this->current_page_field = null;
      $this->sku_field = null;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try {
        $tienda  = \App\Models\Tienda::whereNombre('Hites')->first();
        if (!$tienda) throw new \Exception("Tienda not found", 1);
        $client = new \Goutte\Client();
        //Log::debug("Iniciando barrido de catalogo para ".$tienda->nombre);
        foreach ($this->categories as $category) {
            // code...
            $url = $this->protocol.'://'.$this->uri;
            $current_page = $this->page_start;
            //add page and category in url;
            $url .= "?cgid=$category&srule=price-low-to-high&sz=144&start=$current_page";
            $finished = false;
            try {
              $crawler = $client->request($this->method, $url);
              if ($client->getResponse()->getStatusCode() !== 200) throw new \Exception("Not Valid Request", 1);
              try {
                while (!$finished) {
                  //get list of sku
                  // updated hites catalog
                  $list = $crawler->filter('html > body div.product-tile')->each(function($node) { return $node->attr('data-pid'); });
                  //we got the list id, now append into the DB if we havent got yet
                  if (count($list) === 0) {
                      $finished = true;
                      continue;
                  }
                  foreach ($list as $sku) {
                    try {
                      $product = \App\Models\Producto::where('sku', $sku)->where('id_tienda', $tienda->id)->first();
                      if ((boolean) $product){
                        if ($product->estado == "Detenido") {
                          $product->estado = "Activo";
                          $product->intentos_fallidos = 0;
                          $product->actualizacion_pendiente = 1;
                          $product->save();
                        }
                      } else {
                        $product = \App\Models\Producto::create([
                          'sku' => $sku,
                          'id_tienda' => $tienda->id,
                          'nombre' => $sku,
                          'intervalo_actualizacion' => random_int(15,45),
                          'categoria' => $category
                        ]);
                      }
                    } catch (\Exception $e) {
                      //nothing
                    }
                  }
                  // get next page
                  $current_page = $current_page+144;
                  try {
                    $crawler = $client->request('GET', $this->protocol.'://'.$this->uri."?cgid=$category&srule=price-low-to-high&sz=144&start=$current_page");
                  } catch (\Throwable $th) {
                    $finished = true;
                  }
                  
                }
              } catch (\Exception $e) {
                $finished = true;

              }

            } catch (\Exception $e) {
              //continue
            }
          }
          //Log::debug("Finalizando barrido de catalogo para ".$tienda->nombre);
      } catch (\Exception $e) {
        //calm down
      }
    }
}
