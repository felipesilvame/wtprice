<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\General\Arr as ArrHelper;
use App\Models\SospechaRata;
use App\Helpers\General\Rata;

class FunctionRataFalabella implements ShouldQueue
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
    private $nombre_field;
    private $precio_referencia_field;
    private $precio_oferta_field;
    private $precio_tarjeta_field;
    private $buy_url_field;
    private $image_url_field;
    private $current_page_field;
    private $client;
    private $discount;
    private $webhook;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $category, string $discount, string $webhook)
    {
      $this->categories = $category;
      $this->discount = $discount;
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
      $this->nombre_field = 'displayName';
      $this->precio_referencia_field = 'prices:label,,price.0';
      $this->precio_oferta_field = 'prices:crossed,0,price.0';
      $this->precio_tarjeta_field = 'prices:icons,cmr-icon,price.0';
      $this->buy_url_field = 'url';
      $this->image_url_field = 'https://falabella.scene7.com/is/image/Falabella/';
      $this->webhook = config($webhook);
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
      $tienda = \App\Models\Tienda::whereNombre('Falabella')->first();
      $_d = (string)$this->discount;
      if (!$tienda) return null;
      foreach ($this->categories as $key => $category) {
        try {
          $page_start = 1;
          //get response
          $url = $this->protocol.'://'.$this->uri;
          $url .= "?categoryId=$category&page=$page_start&zone=13&channel=app&sortBy=product.attribute.newIconExpiryDate,desc&f.range.derived.variant.discount=$_d%25+dcto+y+más&f.derived.variant.sellerId=FALABELLA";
          //Log::debug('Getting url: '.$url);
          $response = null;
          $data = null;
          $total_pages = 0;
          try {
            //FALABELLA BLOCKS THE F*KING REQUESTS!!!!
            usleep(1400000);
            if (1) {
              //deprecated, using classic curl
              $response = $client->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9',
                    'Accept'     => 'application/json',
                  ]
              ])->getBody()->getContents();
            }
            /* else {
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_POST, 0);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              $response = curl_exec($ch);
              $err = curl_error($ch);  //if you need
              curl_close($ch);
            } */
          } catch (\Exception $e) {
            Log::warning("SearchRataFalabella: No se ha obtenido respuesta satisfactoria de parte del request".$tienda->nombre);
            throw new \Exception("Error Processing Request", 1);
          }
          if ((boolean) $response) {
            try {
              $data = json_decode($response, true);
            } catch (\Exception $e) {
              Log::warning("SearchRataFalabella: No se ha podido parsear la respuesta de ".$tienda->nombre);
              throw new \Exception("Error Processing Request", 1);
            }
            if (ArrHelper::get($data, 'responseType', 'Success') === 'alt') {
              //Log::debug('1 item: '.ArrHelper::get($data, 'data.altUrl', null));
              //only 1 response, search producto with this sku
              try {
                $url = ArrHelper::get($data, 'data.altUrl', null);
                if (!$url) continue;
                $expl = explode('/', $url);
                $sku = end($expl);

                $producto_original = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                if (!$producto_original) {
                  //create new producto
                  $producto_original  = \App\Models\Producto::create([
                    'id_tienda' => $tienda->id,
                    'sku' => $sku,
                    'nombre' => $sku,
                    'intervalo_actualizacion' => 10,
                    'categoria' => $category
                  ]);
                } else {
                  $producto_original->estado = "Activo";
                  $producto_original->intentos_fallidos = 0;
                  $producto_original->save();
                }

                $sospecha = \App\Models\SospechaRata::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                if(!$sospecha){
                  $sospecha = \App\Models\SospechaRata::create([
                    'id_tienda' => $tienda->id,
                    'nombre_tienda' => $tienda->nombre,
                    'sku' => $sku,
                    'url_compra' => $url,
                    'categoria' => $category,
                  ]);

                  // TODO: Notify sospecha (only url coz its the only what we have)
                  Rata::sospechaRataUrl($url, $this->webhook);
                }
              } catch (\Throwable $th) {
                //throw $th;
              }
              continue;
            }
            //got response, lets check how many pages has this request
            $total_pages = (int)ceil(ArrHelper::get($data, $this->total_elements_field, 0)/ArrHelper::get($data, $this->per_page_field, 1));
            //check now where am i
            $page_start = ArrHelper::get($data, $this->current_page_field, 0);
            //extract all sku for tienda and put them into database
            $results = ArrHelper::get($data, $this->results_field, []);
            //Log::debug('Sospecha Rata: '.count($results).' items');
            foreach ($results as $key => $row) {
              $sku = (string)ArrHelper::get($row, $this->sku_field, null);
              if (!$sku) continue;
              $sospecha = \App\Models\SospechaRata::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
              if (!$sospecha){
                //create new sospecha
                $nombre = null;
                $precio_referencia = null;
                $precio_oferta = null;
                $precio_tarjeta = null;
                $url_compra = null;
                try {
                  $nombre = (string)ArrHelper::get($row, $this->nombre_field, null);

                  $precio_referencia = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_referencia_field, 0));
                  if (!$precio_referencia) $precio_referencia = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, 'state.product.prices:label,,formattedLowestPrice', 0));
                  $precio_oferta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_oferta_field, 0));
                  if (!$precio_oferta) $precio_oferta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, 'state.product.prices:label,(Oferta),formattedLowestPrice', 0));
                  $precio_tarjeta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_tarjeta_field, 0));

                  $url_compra = (string)ArrHelper::get($row, $this->buy_url_field, null);
                  $imagen_url = $tienda->campo_imagen_url.$sku;
                } catch (\Throwable $th) {
                  throw $th;
                }
                $sospecha  = \App\Models\SospechaRata::create([
                  'id_tienda' => $tienda->id,
                  'sku' => $sku,
                  'nombre_producto' => $nombre,
                  'nombre_tienda' => $tienda->nombre,
                  'precio_referencia' => $precio_referencia,
                  'precio_oferta' => $precio_oferta ? $precio_oferta : null,
                  'precio_tarjeta' => $precio_tarjeta ? $precio_tarjeta : null,
                  'categoria' => $category,
                  'url_compra' => $url_compra,
                  'url_imagen' => $imagen_url,
                ]);
                // TODO: Notify sospecha
                  try {
                    Rata::sospechaRata($sospecha, $this->webhook);
                  } catch (\Throwable $th) {
                    //throw $th;
                    //nothing
                  }

                $producto_original = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                if (!$producto_original) {
                  //create new producto
                  $producto_original  = \App\Models\Producto::create([
                    'id_tienda' => $tienda->id,
                    'sku' => $sku,
                    'nombre' => $nombre,
                    'intervalo_actualizacion' => 10,
                    'ultima_actualizacion' => now(),
                    'categoria' => $category
                  ]);
                } else {
                  $producto_original->estado = "Activo";
                  $producto_original->intentos_fallidos = 0;
                  $producto_original->actualizacion_pendiente = 1;
                  $producto_original->intervalo_actualizacion = 10;
                  $producto_original->ultima_actualizacion = now();
                  $producto_original->save();
                }
              }
            }
          }
          //total pages is fullfilled
          //Log::debug("total pages for sospecha rata: $total_pages");
          if ($page_start <= $total_pages) {
            for ($pages = $page_start; $pages <= $total_pages ; $pages++) {
              //FALABELLA BLOCKS THE F*KING REQUESTS!!!!
              usleep(700000);
              //Log::debug("making request for page $pages of $total_pages for cat $category");
              //get response
              $url = $this->protocol.'://'.$this->uri;
              $url .= "?categoryId=$category&page=$pages&zone=13&channel=app&sortBy=product.attribute.newIconExpiryDate,desc&f.range.derived.variant.discount=70%25+dcto+y+más";
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
                  Log::warning("SearchRataFalabella: No se ha podido parsear la respuesta de ".$tienda->nombre);
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
                  $sospecha = \App\Models\SospechaRata::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                  if (!$sospecha){
                    //create new sospecha
                    $nombre = null;
                    $precio_referencia = null;
                    $precio_oferta = null;
                    $precio_tarjeta = null;
                    $url_compra = null;
                    try {
                      $nombre = (string)ArrHelper::get($row, $this->nombre_field, null);
    
                      $precio_referencia = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_referencia_field, 0));
                      if (!$precio_referencia) $precio_referencia = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, 'state.product.prices:label,,formattedLowestPrice', 0));
                      $precio_oferta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_oferta_field, 0));
                      if (!$precio_oferta) $precio_oferta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, 'state.product.prices:label,(Oferta),formattedLowestPrice', 0));
                      $precio_tarjeta = (integer)preg_replace('/[^0-9]/','',(string)ArrHelper::get_pipo($row, $this->precio_tarjeta_field, 0));
    
                      $url_compra = (string)ArrHelper::get($row, $this->buy_url_field, null);
                      $imagen_url = $this->buy_url_field.$sku;
                    } catch (\Throwable $th) {
                      continue;
                    }
                    $sospecha  = \App\Models\SospechaRata::create([
                      'id_tienda' => $tienda->id,
                      'sku' => $sku,
                      'nombre_producto' => $nombre,
                      'nombre_tienda' => $tienda->nombre,
                      'precio_referencia' => $precio_referencia,
                      'precio_oferta' => $precio_oferta ? $precio_oferta : null,
                      'precio_tarjeta' => $precio_tarjeta ? $precio_tarjeta : null,
                      'categoria' => $category,
                      'url_compra' => $url_compra,
                      'url_imagen' => $imagen_url,
                    ]);
                    // TODO: Notify sospecha

                    try {
                      Rata::sospechaRata($sospecha, $this->webhook);
                    } catch (\Throwable $th) {
                      //nothing
                    }
                    $producto_original = \App\Models\Producto::where('id_tienda', $tienda->id)->where('sku', $sku)->first();
                    if (!$producto_original) {
                      //create new producto
                      $producto_original  = \App\Models\Producto::create([
                        'id_tienda' => $tienda->id,
                        'sku' => $sku,
                        'nombre' => $nombre,
                        'intervalo_actualizacion' => 10,
                        'ultima_actualizacion' => now(),
                        'categoria' => $category
                      ]);
                    } else {
                      $producto_original->estado = "Activo";
                      $producto_original->intentos_fallidos = 0;
                      $producto_original->actualizacion_pendiente = 1;
                      $producto_original->intervalo_actualizacion = 10;
                      $producto_original->ultima_actualizacion = now();
                      $producto_original->save();
                    }
                  }
                }
              }
            }
          }
        } catch (\Exception $e) {
          Log::error("Error obteniendo info de ".$tienda->nombre);
          throw $e;
        }
      }
    }
}
