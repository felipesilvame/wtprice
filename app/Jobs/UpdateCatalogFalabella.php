<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\General\Arr as ArrHelper;
use Illuminate\Support\Facades\Log;

/*
*
* Si estas leyendo esto, pido de antemano disculpas,
* ha sido un tremendo chombo querer actualizar de forma automatica los
* catálogos de cada tienda de forma dinamica, por lo que esta parte
* está hardcodeada y variará dependiendo de la tienda, por lo tanto,
* si se incorpora otra tienda además de las creadas en el comando seed:tiendas
* se tendrá que realizar su propio parser
*
*/
class UpdateCatalogFalabella implements ShouldQueue
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
        '/category/cat4850013/Computacion-Gamer',
        '/category/cat720161/Smartphones',
        '/category/cat2018/Celulares-y-Telefonos',
        '/category/cat7190053/Wearables',
        '/category/cat1012/TV',
        '/category/cat70057/Notebooks',
        '/category/cat2062/Monitores',
        '/category/cat40051/All-In-One',
        '/category/cat7230007/Tablets',
        '/category/cat4850013/Computacion-Gamer',
        '/category/cat2023/Videojuegos',
        '/category/cat2038/Fotografia',
      ];
      $this->protocol = 'https';
      $this->method = 'POST';
      $this->uri = 'www.falabella.com/rest/model/falabella/rest/browse/BrowseActor/get-product-record-list';
      $this->page_start = 1;
      $this->total_pages = 1;
      $this->tienda = null;
      $this->total_pages_field = 'state.pagesTotal';
      $this->results_field = 'state.resultList';
      $this->current_page_field = 'state.curentPage';
      $this->sku_field = 'productId';
      $this->client = new \GuzzleHttp\Client();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      if ($this->Tienda()) {
        Log::debug("Tienda found. ID: ".$this->tienda->id);
        // perform the query

        //try foreach category
        foreach ($this->categories as $key => $category) {
          try {
            $this->page_start = 1;
            Log::debug("getting first page for category $category");
            $this->makeRequest($this->page_start, $category);
            //total pages is fullfilled
            Log::debug("total pages for category: $this->total_pages");
            if ($this->page_start <= $this->total_pages) {
              for ($pages = $this->page_start; $pages <= $this->total_pages ; $pages++) {
                Log::debug("making request for page $pages of $this->total_pages for cat $category");
                $this->makeRequest($pages, $category);
              }
            }
          } catch (\Exception $e) {
            Log::error("Error obteniendo info de ".$this->tienda->nombre);
          }

        }

      }
    }

    /*
    *
    * Check if the tienda exists in database,
    * return true and $this->tienda is object,
    * else return false and $this->tienda is null
    *
    */
    private function Tienda(){
      $tienda = \App\Models\Tienda::whereNombre('Falabella')->first();
      if ($tienda) {
        $this->tienda = $tienda;
        return true;
      } else return false;
    }

    private function makeRequest($page, $category){
      $options = [];
      $options['headers'] = [
        'Content-type' => 'application/json',
      ];
      $body_data = [
        'currentPage' => (int)$page,
        'navState' => $category,
      ];
      $options['body'] = json_encode($body_data);
      //get response
      $url = $this->protocol.'://'.$this->uri;
      $response = null;
      $data = null;
      try {
        $response = $this->client->post($url, $options)->getBody()->getContents();
      } catch (\Exception $e) {
        Log::warning("No se ha obtenido respuesta satisfactoria de parte del request".$this->tienda->nombre);
        throw new \Exception("Error Processing Request", 1);
      }
      if ((boolean) $response) {
        try {
          $data = json_decode($response, true);
        } catch (\Exception $e) {
          Log::warning("No se ha podido parsear la respuesta de ".$this->tienda->nombre);
          throw new \Exception("Error Processing Request", 1);
        }
        //got response, lets check how many pages has this request
        $this->total_pages = ArrHelper::get($data, $this->total_pages_field, 0);
        //check now where am i
        $this->page_start = ArrHelper::get($data, $this->current_page_field, 0);
        //extract all sku for tienda and put them into database
        $results = ArrHelper::get($data, $this->results_field, []);
        foreach ($results as $key => $row) {
          $sku = (string)ArrHelper::get($row, $this->sku_field, null);
          if (!$sku) continue;
          $producto = \App\Models\Producto::where('id_tienda', $this->tienda->id)->where('sku', $sku)->first();
          if ((boolean) $producto) continue;
          //create new producto
          $producto  = \App\Models\Producto::create([
            'id_tienda' => $this->tienda->id,
            'sku' => $sku,
            'nombre' => $sku,
            'intervalo_actualizacion' => random_int(4,25)
          ]);
        }
      }
    }
}
