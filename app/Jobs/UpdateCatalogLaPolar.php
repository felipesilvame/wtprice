<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\General\Arr as ArrHelper;
use Illuminate\Support\Facades\Log;

class UpdateCatalogLaPolar implements ShouldQueue
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
        'tecnologia/computadores/notebooks/?srule=new-arrivals',
        'tecnologia/computadores/notebooks-gamer/?srule=new-arrivals',
        'tecnologia/televisores/smart-tv/?srule=new-arrivals',
        'tecnologia/celulares/smartphones/?srule=new-arrivals',
        'tecnologia/videojuegos/play-station/?srule=new-arrivals',
        'tecnologia/videojuegos/nintendo/?srule=new-arrivals',
        'tecnologia/videojuegos/xbox/?srule=new-arrivals',
        'tecnologia/computadores/tablet/?srule=new-arrivals'
      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'www.lapolar.cl/';
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
        $tienda  = \App\Models\Tienda::whereNombre('LaPolar')->first();
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
              //get list of sku
              $list = $crawler->filter('html > body div.product-tile__wrapper')->each(function($node) { return $node->attr('data-pid'); });
              //we got the list id, now append into the DB if we havent got yet
              foreach ($list as $sku) {
                try {
                  $product = \App\Models\Producto::where('sku', $sku)->where('id_tienda', $tienda->id)->first();
                  if ((boolean) $product){
                    if ($product->estado == "Detenido") {
                      $product->estado = "Activo";
                      $product->intentos_fallidos = 0;
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
