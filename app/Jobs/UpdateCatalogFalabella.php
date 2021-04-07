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
    private $total_elements_field;
    private $per_page_field;
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
        'cat4850013', ///Computacion-Gamer',
        'cat720161', //Smartphones',
        'cat2018', //Celulares-y-Telefonos',
        'cat7190053', //Wearables',
        'cat1012', //TV',
        'cat70057', //Notebooks',
        'cat2062', //Monitores',
        'cat40051', //All-In-One',
        'cat7230007', //Tablets',
        //'cat4850013', //Computacion-Gamer',
        'cat2023', //Videojuegos',
        'cat2038', //Fotografia',
      ];
      $this->protocol = 'https';
      $this->method = 'GET';
      $this->uri = 'www.falabella.com/s/browse/v1/listing/cl';
      $this->page_start = 1;
      $this->total_pages = 1;
      $this->tienda = null;
      $this->total_elements_field = 'data.pagination.count';
      $this->per_page_field = 'data.pagination.perPage';
      $this->results_field = 'data.results';
      $this->current_page_field = 'data.pagination.currentPage';
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
      $total_pages = 0;
      if ($tienda = \App\Models\Tienda::whereNombre('Falabella')->first()) {
        //Log::debug("Iniciando barrido de catalogo para ".$tienda->nombre);
        //Log::debug("Tienda found. ID: ".$tienda->id);
        // perform the query
        //try foreach category
        foreach ($this->categories as $key => $category) {
          try {
            $page_start = 1;
            //Log::debug("getting first page for category $category");
            
            //get response
            $url = $this->protocol.'://'.$this->uri;
            $url .= "?categoryId=$category&page=$page_start&zone=13&channel=app&sortBy=product.attribute.newIconExpiryDate,desc";
            $response = null;
            $data = null;
            $total_pages = 0;
            try {
              //deprecated, using classic curl
              //$response = $client->get($url)->getBody()->getContents();
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_POST, 0);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              $response = curl_exec($ch);
              $err = curl_error($ch);  //if you need
              curl_close($ch);
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
              $total_pages = (int)ceil(ArrHelper::get($data, $this->total_elements_field, 0)/ArrHelper::get($data, $this->per_page_field, 1));
              //check now where am i
              $page_start = ArrHelper::get($data, $this->current_page_field, 0);
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
            if ($page_start <= $total_pages) {
              for ($pages = $page_start; $pages <= $total_pages ; $pages++) {
                //FALABELLA BLOCKS THE F*KING REQUESTS!!!!
                usleep(700000);
                //Log::debug("making request for page $pages of $total_pages for cat $category");
                //get response
                $url = $this->protocol.'://'.$this->uri;
                $url .= "?categoryId=$category&page=$pages&zone=13&channel=app&sortBy=product.attribute.newIconExpiryDate%2Cdesc";
                $response = null;
                $data = null;
                try {
                  //deprecated, using classic curl
                  //$response = $client->get($url)->getBody()->getContents();
                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_URL, $url);
                  curl_setopt($ch, CURLOPT_POST, 0);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  $response = curl_exec($ch);
                  $err = curl_error($ch);  //if you need
                  curl_close($ch);
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
