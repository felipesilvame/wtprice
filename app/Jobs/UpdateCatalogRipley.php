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
class UpdateCatalogRipley implements ShouldQueue
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
        '945203', // notebooks
        '945204', // 2 en 1 convertibles
        '945205', //notebooks gamer
        '945206', //tablets
        '945220', //parlantes
        '947841', //gadgets
        '945153', //television
        '945155', //mundo gamer
        '945157', //telefonia
        '945158', //computacion
      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'www.ripley.cl/wcs/resources/store/10151/productview/bySearchTerm/';
      $this->page_start = 0;
      $this->total_pages = 0;
      $this->tienda = null;
      $this->total_pages_field = 'recordSetTotal';
      $this->results_field = 'CatalogEntryView';
      $this->current_page_field = 'recordSetStartNumber';
      $this->sku_field = 'partNumber';
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
      $options = [];
      //added proxy for ripley
      if ((boolean)env('APP_PROXY')) {
        $options['proxy'] = env('APP_PROXY');
$options['verify'] = false;
      }
      if ($tienda = \App\Models\Tienda::whereNombre('Ripley')->first()) {
        Log::debug("Tienda found. ID: ".$tienda->id);
        // perform the query
        //try foreach category
        foreach ($this->categories as $key => $category) {
          try {
            $page_start = 1;
            Log::debug("getting first page for category $category");
            //get response
            $url = $this->protocol.'://'.$this->uri;
            //add page and category in url;
            $url .= "*?categoryId=$category&pageSize=50&pageNumber=$page_start&facet=xquantity:({1+*}+1)";
            $response = null;
            $data = null;
            try {
              $response = $client->get($url, $options)->getBody()->getContents();
            } catch (\Exception $e) {
              Log::warning("No se ha obtenido respuesta satisfactoria de parte del request".$tienda->nombre);
              throw new \Exception("Error Processing Request", 1);
            }
            if ((boolean) $response) {
              try {
                $data = json_decode($response, true);
              } catch (\Exception $e) {
                Log::warning("No se ha podido parsear la respuesta de ".$tienda->nombre);
                throw new \Exception("Error Processing Request", 1);
              }
              //got response, lets check how many pages has this request
              $total_pages = (int)ceil((float)ArrHelper::get($data, $this->total_pages_field, 0)/(float)50);
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
              for ($pages = $page_start+1; $pages <= $total_pages ; $pages++) {
                Log::debug("making request for page $pages of $total_pages for cat $category");
                //get response
                $url = $this->protocol.'://'.$this->uri;
                //add page and category in url;
                $url .= "*?categoryId=$category&pageSize=50&pageNumber=$pages&facet=xquantity:({1+*}+1)";
                $response = null;
                $data = null;
                try {
                  //added proxy for ripley
                  $response = $client->get($url, $options)->getBody()->getContents();
                } catch (\Exception $e) {
                  Log::warning("No se ha obtenido respuesta satisfactoria de parte del request".$tienda->nombre);
                  throw new \Exception("Error Processing Request", 1);
                }
                if ((boolean) $response) {
                  try {
                    $data = json_decode($response, true);
                  } catch (\Exception $e) {
                    Log::warning("No se ha podido parsear la respuesta de ".$tienda->nombre);
                    throw new \Exception("Error Processing Request", 1);
                  }
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
                    }
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
            }
          } catch (\Exception $e) {
            Log::error("Error obteniendo info de ".$tienda->nombre);
          }
        }
      }
    }
}
