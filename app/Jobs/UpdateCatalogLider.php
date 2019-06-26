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
class UpdateCatalogLider implements ShouldQueue
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
        'Videojuegos',
        'Televisores',
        'Smartphones',
        'Computación',
        'Smartwatch',
      ];
      $this->protocol = 'https';
      $this->method = 'POST';
      $this->uri = '529cv9h7mw-dsn.algolia.net/1/indexes/*/queries?x-algolia-agent=Algolia%20for%20vanilla%20JavaScript%20(lite)%203.32.1%3Breact-instantsearch%205.4.0%3BJS%20Helper%202.26.1&x-algolia-application-id=529CV9H7MW&x-algolia-api-key=c6ab9bc3e19c260e6bad42abe143d5f4';
      $this->page_start = 0;
      $this->total_pages = 0;
      $this->tienda = null;
      $this->total_pages_field = 'results.0.nbPages';
      $this->results_field = 'results.0.hits';
      $this->current_page_field = 'results.0.page';
      $this->sku_field = 'sku';
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
        Log::debug("Tienda found. ID: $this->tienda->id");
        // perform the query

        //try foreach category
        foreach ($this->categories as $key => $category) {
          try {
            $this->page_start = 0;
            Log::debug("getting first page for category $category");
            $this->makeRequest($this->page_start, $category);
            //total pages is fullfilled
            Log::debug("total pages for category: $this->total_pages");
            if ($this->page_start < $this->total_pages) {
              for ($pages = $this->page_start+1; $pages < $this->total_pages ; $pages++) {
                Log::debug("making request for page $pages of $this->total_pages for cat $category");
                $this->makeRequest($pages, $category);
              }
            }
          } catch (\Exception $e) {
            Log::error("Error obteniendo info de Lider");
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
      $tienda = \App\Models\Tienda::whereNombre('Lider')->first();
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
      $body_data = ['requests' => [
        [
          'indexName' => 'campaigns_production',
          'params' => "query=&hitsPerPage=50&maxValuesPerFacet=50&page=$page&facets=[\"categorias\",\"filters.Marca\",\"filters.Producto\"]&tagFilters=&facetFilters=[\"categorias:$category\"]"
          ]
        ]
      ];
      $options['body'] = json_encode($body_data);
      //get response
      $url = $this->protocol.'://'.$this->uri;
      $response = null;
      $data = null;
      try {
        $response = $this->client->post($url, $options)->getBody()->getContents();
      } catch (\Exception $e) {
        Log::warning("No se ha obtenido respuesta satisfactoria de parte del request Lider");
        throw new \Exception("Error Processing Request", 1);
      }
      if ((boolean) $response) {
        try {
          $data = json_decode($response, true);
        } catch (\Exception $e) {
          Log::warning("No se ha podido parsear la respuesta de Lider");
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
