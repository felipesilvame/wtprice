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
class UpdateCatalogCorona implements ShouldQueue
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
        '/9/35', // televisores
        '/9/54', // cámaras
        '/9/39/40/', //notebook
        '/9/39/41/', //All in one
        '/9/39/42/', //tablets
        '/9/56/', //consolas
        '/8/122/124', //telefonos
      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'www.corona.cl/api/catalog_system/pub/products/search/';
      $this->page_start = 0;
      $this->page_end = 0;
      $this->total_pages = 0;
      $this->tienda = null;
      $this->total_pages_field = null;
      $this->results_field = null;
      $this->current_page_field = null;
      $this->sku_field = 'productId';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $client = new \GuzzleHttp\Client();
      $tienda = null;
      if ($tienda = \App\Models\Tienda::whereNombre('Corona')->first()) {
        //Log::debug("Iniciando barrido de catalogo para ".$tienda->nombre);
        //Log::debug("Tienda found. ID: ".$tienda->id);
        // perform the query

        //try foreach category
        foreach ($this->categories as $key => $category) {
          try {
            $page_start = 0;
            $from = 0;
            $to = 49;
            //Log::debug("getting first page for category $category");
            //get response
            $url = $this->protocol.'://'.$this->uri;
            //add page and category in url;
            $url .= "?fq=C:$category&_from=$from&_to=$to";
            $response = null;
            $data = null;
            $total_pages = 0;
            try {
              $res = $client->get($url);
              $response = $res->getBody()->getContents();
              $total_pages = (int)ceil((float)explode("/",$res->getHeader('resources')[0])[1]/(float)50);
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
              //$this->total_pages = (int)ceil((float)ArrHelper::get($data, $this->total_pages_field, 0)/(float)20);
              //check now where am i
              //$this->page_start = (int)ceil((float)ArrHelper::get($data, $this->current_page_field, 0)/(float)50) +1;
              //extract all sku for tienda and put them into database
              $results = $data;//ArrHelper::get($data, $this->results_field, []);
              foreach ($results as $key => $row) {
                $sku = (string)ArrHelper::get($row, $this->sku_field, null);
                if (!$sku) continue;
                $producto = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                if ((boolean) $producto){
                  if ($producto->estado == "Detenido") {
                    $producto->estado = "Activo";
                    $producto->intentos_fallidos = 0;
                    $producto->actualizacion_pendiente = 1;
                    $producto->save();
                  }
                  continue;
                } else {
                  //create new producto
                  $producto  = \App\Models\Producto::create([
                    'id_tienda' => $tienda->id,
                    'sku' => $sku,
                    'nombre' => $sku,
                    'intervalo_actualizacion' => random_int(15,45),
                    'categoria' => $category
                  ]);
                }
              }
            }
            //total pages is fullfilled
            //Log::debug("total pages for category: $total_pages");
            if ($page_start < $total_pages) {
              for ($pages = $page_start+1; $pages <= $total_pages ; $pages++) {
                //Log::debug("making request for page $pages of $total_pages for cat $category");
                $from = $to+1;
                $to += 50;
                //get response
                $url = $this->protocol.'://'.$this->uri;
                //add page and category in url;
                $url .= "?fq=C:$category&_from=$from&_to=$to";
                $response = null;
                $data = null;
                try {
                  $res = $client->get($url);
                  $response = $res->getBody()->getContents();
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
                  //$this->total_pages = (int)ceil((float)ArrHelper::get($data, $this->total_pages_field, 0)/(float)20);
                  //check now where am i
                  //$this->page_start = (int)ceil((float)ArrHelper::get($data, $this->current_page_field, 0)/(float)50) +1;
                  //extract all sku for tienda and put them into database
                  $results = $data;//ArrHelper::get($data, $this->results_field, []);
                  foreach ($results as $key => $row) {
                    $sku = (string)ArrHelper::get($row, $this->sku_field, null);
                    if (!$sku) continue;
                    $producto = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                    if ((boolean) $producto){
                      if ($producto->estado == "Detenido") {
                        $producto->estado = "Activo";
                        $producto->intentos_fallidos = 0;
                        $producto->actualizacion_pendiente = 1;
                        $producto->save();
                      }
                      continue;
                    }
                    //create new producto
                    $producto  = \App\Models\Producto::create([
                      'id_tienda' => $tienda->id,
                      'sku' => $sku,
                      'nombre' => $sku,
                      'intervalo_actualizacion' => random_int(15,45),
                      'categoria' => $category
                    ]);
                  }
                }
              }
            }
          } catch (\Exception $e) {
            Log::error("Error obteniendo info de ".$tienda->nombre);
          }
        }
        //Log::debug("Finalizando barrido de catalogo para ".$tienda->nombre);
      }
    }
}
