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
        'tecnologia/tv-video/todos-los-led?pageSize=48&orderBy=2',
        'tecnologia/computacion?pageSize=48&orderBy=2',
        'tecnologia/video-juego/consolas?pageSize=48&orderBy=2',
        'tecnologia/video-juego/juegos?pageSize=48&orderBy=2',
        'tecnologia/video-juego/suscripciones-y-creditos?pageSize=48&orderBy=2',
        'tecnologia/video-juego/play-station?pageSize=48&orderBy=2',
        'tecnologia/video-juego/xbox?pageSize=48&orderBy=2',
        'tecnologia/video-juego/nintendo?pageSize=48&orderBy=2',
        'tecnologia/fotografia/camaras-reflex?pageSize=48&orderBy=2',
        'tecnologia/fotografia/camaras-semi-profesional?pageSize=48&orderBy=2',
        'tecnologia/fotografia/camaras-deportivas?pageSize=48&orderBy=2',
        'celulares/smartphone?pageSize=48&orderBy=2',

      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'www.hites.com/';
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
        $tienda  = \App\Models\Tienda::whereNombre('Hites')->first();
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
                  $data = json_decode($crawler->filter('script#hy-data')->first()->text(), true);$list = collect(Arr::get($data, 'result.products', []))->pluck('partNumber');
                  //we got the list id, now append into the DB if we havent got yet
                  if ($list->count() === 0) {
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
                  }
                  // get next page
                  $current_page++;
                  try {
                    $crawler = $client->request('GET', $url.'&page='.$current_page);
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
      } catch (\Exception $e) {
        //calm down
      }
    }
}
