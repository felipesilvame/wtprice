<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\General\Arr as ArrHelper;
use Illuminate\Support\Facades\Log;

class UpdateCatalogParis implements ShouldQueue
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
        'electro/television/?srule=nuevosproductos',
        'tecnologia/celulares/smartphones/?srule=nuevosproductos',
        'tecnologia/computadores/?srule=nuevosproductos',
        'tecnologia/gamer/consolas/?srule=nuevosproductos',
        'tecnologia/computadores/pc-gamer/?srule=nuevosproductos',
        'tecnologia/accesorios-videojuegos/juegos-ps4-vr/?srule=nuevosproductos',
        'tecnologia/accesorios-videojuegos/juegos-nintendo/?srule=nuevosproductos'
      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'www.paris.cl/';
      $this->page_start = 1;
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
          $tienda  = \App\Models\Tienda::whereNombre('Paris')->first();
          if (!$tienda) throw new \Exception("Tienda not found", 1);
          $client = new \Goutte\Client();

          foreach ($this->categories as $category) {
            // code...
            $url = $this->protocol.'://'.$this->uri.$category;
            $current_page = $this->page_start;
            $finished = false;
            try {
              $crawler = $client->request($this->method, $url);
              if ($client->getResponse()->getStatusCode() !== 200) throw new \Exception("Not Valid Request", 1);
              try {
                while (!$finished) {
                  //get list of sku
                  $list = $crawler->filter('html > body div.list-products div.product-tile')->each(function($node) { return $node->attr('data-itemid'); });
                  //we got the list id, now append into the DB if we havent got yet
                  foreach ($list as $sku) {
                    try {
                      $product = \App\Models\Producto::where('sku', $sku)->where('id_tienda', $tienda->id)->first();
                      if ((boolean) $product){
                        if ($product->estado == "Detenido") {
                          $product->estado = "Activo";
                          $product->intentos_fallidos = 0;
                          $producto->actualizacion_pendiente = 1;
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
                    //now get the next link page
                    $current_page +=1;
                  }
                  // get next page
                  $crawler = $client->click($crawler->filter("html > body a.pag.page-$current_page")->first()->link());
                }
              } catch (\Exception $e) {
                throw $e;

              }

            } catch (\Exception $e) {
              //continue
            }
          }
        } catch (\Exception $e) {
          //calm down
        }

    }
}
