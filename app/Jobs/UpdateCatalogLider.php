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
        'Consolas',
        'Televisores',
        'Smartphones',
        'Computación',
        'Smartwatch',
        'Fotografía'
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
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $tienda = null;
      $client = new \GuzzleHttp\Client();
      if ($tienda = \App\Models\Tienda::whereNombre('Lider')->first()) {
        Log::debug("Tienda found. ID: $tienda->id");
        // perform the query
        //try foreach category
        foreach ($this->categories as $key => $category) {
          try {
            $page_start = 0;
            Log::debug("getting first page for category $category");
            $options = [];
            $options['headers'] = [
              'Content-type' => 'application/json',
            ];
            $body_data = ['requests' => [
              [
                'indexName' => 'campaigns_production',
                'params' => "query=&hitsPerPage=50&maxValuesPerFacet=50&page=$page_start&facets=[\"categorias\",\"filters.Marca\",\"filters.Producto\"]&tagFilters=&facetFilters=[\"categorias:$category\"]"
                ]
              ]
            ];
            $options['body'] = json_encode($body_data);
            //get response
            $url = $this->protocol.'://'.$this->uri;
            $response = null;
            $data = null;
            try {
              $response = $client->post($url, $options)->getBody()->getContents();
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
              $total_pages = ArrHelper::get($data, $this->total_pages_field, 0);
              //extract all sku for tienda and put them into database
              $results = ArrHelper::get($data, $this->results_field, []);
              foreach ($results as $key => $row) {
                $sku = (string)ArrHelper::get($row, $this->sku_field, null);
                if (!$sku) continue;
                $producto = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                if ((boolean) $producto){
                  if ($producto->estado == "Detenido") {
                    $producto->estado = "Activo";
                    $producto->intentos_fallidos = 0;
                    $producto->save();
                  }
                  continue;
                } else {
                  //create new producto
                  $producto  = \App\Models\Producto::create([
                    'id_tienda' => $tienda->id,
                    'sku' => $sku,
                    'nombre' => $sku,
                    'intervalo_actualizacion' => random_int(15,45)
                  ]);
                }
              }
            }
            //total pages is fullfilled
            Log::debug("total pages for category: $total_pages");
            if ($page_start < $total_pages) {
              for ($pages = $page_start+1; $pages < $total_pages ; $pages++) {
                Log::debug("making request for page ".($pages+1)." of $total_pages for cat $category");
                $options = [];
                $options['headers'] = [
                  'Content-type' => 'application/json',
                ];
                $body_data = ['requests' => [
                  [
                    'indexName' => 'campaigns_production',
                    'params' => "query=&hitsPerPage=50&maxValuesPerFacet=50&page=$pages&facets=[\"categorias\",\"filters.Marca\",\"filters.Producto\"]&tagFilters=&facetFilters=[\"categorias:$category\"]"
                    ]
                  ]
                ];
                $options['body'] = json_encode($body_data);
                //get response
                $url = $this->protocol.'://'.$this->uri;
                $response = null;
                $data = null;
                try {
                  $response = $client->post($url, $options)->getBody()->getContents();
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
                  //extract all sku for tienda and put them into database
                  $results = ArrHelper::get($data, $this->results_field, []);
                  foreach ($results as $key => $row) {
                    $sku = (string)ArrHelper::get($row, $this->sku_field, null);
                    if (!$sku) continue;
                    $producto = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                    if ((boolean) $producto){
                      if ($producto->estado == "Detenido") {
                        $producto->estado = "Activo";
                        $producto->intentos_fallidos = 0;
                        $producto->save();
                      }
                      continue;
                    } else {
                      //create new producto
                      $producto  = \App\Models\Producto::create([
                        'id_tienda' => $tienda->id,
                        'sku' => $sku,
                        'nombre' => $sku,
                        'intervalo_actualizacion' => random_int(15,45)
                      ]);
                      \Notification::route('slack', env('SLACK_WEBHOOK_URL'))
                      ->notify(new \App\Notifications\ProductAdded($producto));
                    }
                  }
                }

              }
            }
          } catch (\Exception $e) {
            Log::error("Error obteniendo info de Lider");
          }
        }
      }
    }
}
